<?php
// Incluir archivo de configuración
require_once '../config.php';

// Verificar si el usuario está logueado y es tipo gimnasio
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Obtener el ID del gimnasio de la sesión
$gimnasio_id = $_SESSION['gimnasio_id'];

// Obtener barrios
$stmt = $pdo->prepare("SELECT * FROM barrios WHERE deleted = 0 ORDER BY barrio");
$stmt->execute();
$barrios = $stmt->fetchAll();

// Obtener localidades
$stmt = $pdo->prepare("SELECT * FROM localidades WHERE deleted = 0 ORDER BY localidad");
$stmt->execute();
$localidades = $stmt->fetchAll();

// Obtener membresías del gimnasio actual
$stmt = $pdo->prepare("SELECT * FROM membresias WHERE idgimnasio = ? AND deleted = 0 ORDER BY membresia");
$stmt->execute([$gimnasio_id]);
$membresias = $stmt->fetchAll();

$is_editing = false;
$miembro_id = null;
$miembro = []; // Para almacenar los datos del miembro en modo edición

// Inicializar variables del formulario
$miembro_nombre = '';
$dni = '';
$telefono = '';
$correo = '';
$idbarrio = '';
$idlocalidad = '';
$idmembresia = '';

// Detectar si es modo edición
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $miembro_id = intval($_GET['id']);
    
    // Verificar que el miembro pertenezca al gimnasio actual
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idmiembro = ? AND idgimnasio = ? AND deleted = 0");
    $stmt->execute([$miembro_id, $gimnasio_id]);
    if ($stmt->fetchColumn() == 0) {
        header("Location: miembros.php");
        exit;
    }

    // Obtener datos del miembro
    $stmt = $pdo->prepare("
        SELECT m.*, b.barrio, l.localidad, mem.membresia 
        FROM miembros m
        JOIN barrios b ON m.idbarrio = b.idbarrio
        JOIN localidades l ON m.idlocalidad = l.idlocalidad
        JOIN membresias mem ON m.idmembresia = mem.idmembresia
        WHERE m.idmiembro = ? AND m.idgimnasio = ? AND m.deleted = 0
    ");
    $stmt->execute([$miembro_id, $gimnasio_id]);
    $miembro = $stmt->fetch();

    if ($miembro) {
        $is_editing = true;
        // Asignar datos del miembro a variables para pre-llenar el formulario
        $miembro_nombre = $miembro['miembro'];
        $dni = $miembro['dni'];
        $telefono = $miembro['telefono'];
        $correo = $miembro['correo'];
        $idbarrio = $miembro['idbarrio'];
        $idlocalidad = $miembro['idlocalidad'];
        $idmembresia = $miembro['idmembresia'];
    } else {
        // Miembro no encontrado o no pertenece al gimnasio, redirigir
        header("Location: miembros.php");
        exit;
    }
}

// Procesar el formulario si se ha enviado
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y limpiar los datos de entrada
    $miembro_nombre = limpiarDato($_POST['miembro']);
    $dni = limpiarDato($_POST['dni']);
    $telefono = limpiarDato($_POST['telefono']);
    $correo = limpiarDato($_POST['correo']);
    $idbarrio = intval($_POST['idbarrio']);
    $idlocalidad = intval($_POST['idlocalidad']);
    $idmembresia = intval($_POST['idmembresia']);
    
    // Validaciones básicas
    if (empty($miembro_nombre) || empty($dni) || empty($telefono) || empty($correo) || $idbarrio <= 0 || $idlocalidad <= 0 || $idmembresia <= 0) {
        $error = "Todos los campos son obligatorios";
    } else {
        // Verificar que la membresía pertenezca al gimnasio
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM membresias WHERE idmembresia = ? AND idgimnasio = ? AND deleted = 0");
        $stmt->execute([$idmembresia, $gimnasio_id]);
        if ($stmt->fetchColumn() == 0) {
            $error = "La membresía seleccionada no es válida para este gimnasio";
        } else {
            try {
                if ($is_editing) {
                    // Actualizar el miembro
                    $stmt = $pdo->prepare("
                        UPDATE miembros 
                        SET miembro = ?, dni = ?, telefono = ?, correo = ?, idbarrio = ?, idlocalidad = ?, idmembresia = ? , idultimopago = ?
                        WHERE idmiembro = ? AND idgimnasio = ?
                    ");
                    $stmt->execute([$miembro_nombre, $dni, $telefono, $correo, $idbarrio, $idlocalidad, $idmembresia, $miembro_id, $gimnasio_id]);
                    
                    $success = "Miembro actualizado correctamente";
                    
                    // Actualizar datos del miembro después de la actualización
                    $stmt = $pdo->prepare("
                        SELECT m.*, b.barrio, l.localidad, mem.membresia 
                        FROM miembros m
                        JOIN barrios b ON m.idbarrio = b.idbarrio
                        JOIN localidades l ON m.idlocalidad = l.idlocalidad
                        JOIN membresias mem ON m.idmembresia = mem.idmembresia
                        WHERE m.idmiembro = ? AND m.idgimnasio = ? AND m.deleted = 0
                    ");
                    $stmt->execute([$miembro_id, $gimnasio_id]);
                    $miembro = $stmt->fetch();
                } else {
                    // Generar una contraseña por defecto (puede ser el DNI o una contraseña genérica)
                    $password = password_hash('password', PASSWORD_DEFAULT);
                    
                    // Insertar el nuevo miembro
                    $stmt = $pdo->prepare("
                        INSERT INTO miembros (miembro, dni, telefono, correo, idbarrio, idlocalidad, estado, idmembresia, estado_membresia, idgimnasio, password, deleted) 
                        VALUES (?, ?, ?, ?, ?, ?, 0, ?, 1, ?, ?, 0)
                    ");
                    $stmt->execute([$miembro_nombre, $dni, $telefono, $correo, $idbarrio, $idlocalidad, $idmembresia, $gimnasio_id, $password]);
                    
                    $success = "Miembro agregado correctamente";
                    
                    // Limpiar el formulario después de agregar
                    $miembro_nombre = '';
                    $dni = '';
                    $telefono = '';
                    $correo = '';
                    $idbarrio = '';
                    $idlocalidad = '';
                    $idmembresia = '';
                }
            } catch (PDOException $e) {
                $error = "Error al procesar el miembro: " . $e->getMessage();
            }
        }
    }
}

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'Editar Miembro' : 'Agregar Miembro'; ?> - Sistema de Gestión de Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            padding-top: 60px;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .nav-link {
            color: #ced4da;
            font-weight: 500;
            padding: .5rem 1rem;
        }
        .nav-link:hover {
            color: #fff;
        }
        .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 240px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
                padding: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">GymSystem</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-dumbbell"></i> <?php echo htmlspecialchars($nombre_gimnasio); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../logout.php">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="miembros.php">
                                <i class="fas fa-users"></i> Miembros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rutinas.php">
                                <i class="fas fa-dumbbell"></i> Rutinas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="membresias.php">
                                <i class="fas fa-id-card"></i> Membresías
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><?php echo $is_editing ? 'Editar Miembro' : 'Agregar Nuevo Miembro'; ?></h2>
                        <a href="miembros.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Formulario de Miembro</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_editing ? "?id=" . $miembro_id : "")); ?>">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="miembro" class="form-label">Nombre Completo</label>
                                        <input type="text" class="form-control" id="miembro" name="miembro" value="<?php echo htmlspecialchars($miembro_nombre); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dni" class="form-label">DNI</label>
                                        <input type="text" class="form-control" id="dni" name="dni" value="<?php echo htmlspecialchars($dni); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="correo" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="idbarrio" class="form-label">Barrio</label>
                                        <select class="form-select" id="idbarrio" name="idbarrio" required>
                                            <option value="">Seleccione un barrio</option>
                                            <?php foreach ($barrios as $barrio): ?>
                                                <option value="<?php echo $barrio['idbarrio']; ?>" <?php echo ($idbarrio == $barrio['idbarrio']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($barrio['barrio']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="idlocalidad" class="form-label">Localidad</label>
                                        <select class="form-select" id="idlocalidad" name="idlocalidad" required>
                                            <option value="">Seleccione una localidad</option>
                                            <?php foreach ($localidades as $localidad): ?>
                                                <option value="<?php echo $localidad['idlocalidad']; ?>" <?php echo ($idlocalidad == $localidad['idlocalidad']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($localidad['localidad']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="idmembresia" class="form-label">Membresía</label>
                                    <select class="form-select" id="idmembresia" name="idmembresia" required>
                                        <option value="">Seleccione una membresía</option>
                                        <?php foreach ($membresias as $membresia): ?>
                                            <option value="<?php echo $membresia['idmembresia']; ?>" <?php echo ($idmembresia == $membresia['idmembresia']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($membresia['membresia']); ?> - $<?php echo $membresia['precio']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">Cancelar</button>
                                    <button type="submit" class="btn btn-primary"><?php echo $is_editing ? 'Guardar Cambios' : 'Guardar Miembro'; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


