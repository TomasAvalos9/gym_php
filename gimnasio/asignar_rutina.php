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

// Verificar si se ha proporcionado un ID de miembro y un ID de rutina
if (!isset($_GET['miembro_id']) || empty($_GET['miembro_id'])) {
    header("Location: miembros.php");
    exit;
}

$miembro_id = intval($_GET['miembro_id']);

// Verificar que el miembro pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idmiembro = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$miembro_id, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: miembros.php");
    exit;
}

// Obtener datos del miembro
$stmt = $pdo->prepare("SELECT miembro FROM miembros WHERE idmiembro = ?");
$stmt->execute([$miembro_id]);
$nombre_miembro = $stmt->fetchColumn();

// Obtener rutinas del gimnasio que no estén asignadas al miembro
$stmt = $pdo->prepare("
    SELECT r.* 
    FROM rutinas r
    WHERE r.idgimnasio = ? 
    AND r.deleted = 0
    AND r.idrutina NOT IN (
        SELECT rm.idrutina 
        FROM rutinas_miembro rm 
        WHERE rm.idmiembro = ?
    )
    ORDER BY r.rutina
");
$stmt->execute([$gimnasio_id, $miembro_id]);
$rutinas_disponibles = $stmt->fetchAll();

// Procesar el formulario si se ha enviado
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['idrutina']) && !empty($_POST['idrutina'])) {
        $idrutina = intval($_POST['idrutina']);
        
        // Verificar que la rutina pertenezca al gimnasio
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas WHERE idrutina = ? AND idgimnasio = ? AND deleted = 0");
        $stmt->execute([$idrutina, $gimnasio_id]);
        if ($stmt->fetchColumn() == 0) {
            $error = "La rutina seleccionada no es válida para este gimnasio";
        } else {
            try {
                // Verificar si ya existe la asignación
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas_miembro WHERE idmiembro = ? AND idrutina = ?");
                $stmt->execute([$miembro_id, $idrutina]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Esta rutina ya está asignada al miembro";
                } else {
                    // Asignar la rutina al miembro
                    $stmt = $pdo->prepare("INSERT INTO rutinas_miembro (idmiembro, idrutina) VALUES (?, ?)");
                    $stmt->execute([$miembro_id, $idrutina]);
                    
                    $success = "Rutina asignada correctamente";
                    
                    // Actualizar la lista de rutinas disponibles
                    $stmt = $pdo->prepare("
                        SELECT r.* 
                        FROM rutinas r
                        WHERE r.idgimnasio = ? 
                        AND r.deleted = 0
                        AND r.idrutina NOT IN (
                            SELECT rm.idrutina 
                            FROM rutinas_miembro rm 
                            WHERE rm.idmiembro = ?
                        )
                        ORDER BY r.rutina
                    ");
                    $stmt->execute([$gimnasio_id, $miembro_id]);
                    $rutinas_disponibles = $stmt->fetchAll();
                }
            } catch (PDOException $e) {
                $error = "Error al asignar la rutina: " . $e->getMessage();
            }
        }
    } else {
        $error = "Debe seleccionar una rutina";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Rutina - Sistema de Gestión de Gimnasio</title>
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
                            <i class="fas fa-dumbbell"></i> <?php 
                            $stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
                            $stmt->execute([$_SESSION['gimnasio_id']]);
                            echo htmlspecialchars($stmt->fetchColumn());
                            ?>
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
                        <h2>Asignar Rutina a Miembro</h2>
                        <a href="detalle_miembro.php?id=<?php echo $miembro_id; ?>" class="btn btn-secondary">
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
                            <h5 class="mb-0">Asignar Rutina a <?php echo htmlspecialchars($nombre_miembro); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($rutinas_disponibles) > 0): ?>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?miembro_id=" . $miembro_id); ?>">
                                    <div class="mb-3">
                                        <label for="idrutina" class="form-label">Seleccione una Rutina</label>
                                        <select class="form-select" id="idrutina" name="idrutina" required>
                                            <option value="">-- Seleccione una rutina --</option>
                                            <?php foreach ($rutinas_disponibles as $rutina): ?>
                                                <option value="<?php echo $rutina['idrutina']; ?>"><?php echo htmlspecialchars($rutina['rutina']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">Asignar Rutina</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p>No hay rutinas disponibles para asignar a este miembro. Todas las rutinas ya han sido asignadas o no hay rutinas registradas.</p>
                                    <div class="mt-3">
                                        <a href="agregar_rutina.php" class="btn btn-primary">
                                            <i class="fas fa-plus-circle"></i> Crear Nueva Rutina
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
