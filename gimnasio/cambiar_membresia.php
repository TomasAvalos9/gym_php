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

// Verificar si se ha proporcionado un ID de miembro
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: miembros.php");
    exit;
}

$miembro_id = intval($_GET['id']);

// Verificar que el miembro pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT * FROM miembros WHERE idmiembro = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$miembro_id, $gimnasio_id]);
$miembro = $stmt->fetch();

if (!$miembro) {
    header("Location: miembros.php");
    exit;
}

// Obtener todas las membresías del gimnasio
$stmt = $pdo->prepare("SELECT * FROM membresias WHERE idgimnasio = ? AND deleted = 0 ORDER BY membresia");
$stmt->execute([$gimnasio_id]);
$membresias = $stmt->fetchAll();

// Obtener la membresía actual del miembro
$stmt = $pdo->prepare("SELECT * FROM membresias WHERE idmembresia = ? AND idgimnasio = ?");
$stmt->execute([$miembro['idmembresia'], $gimnasio_id]);
$membresia_actual = $stmt->fetch();

// Procesar el formulario si se ha enviado
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nueva_membresia_id = intval($_POST['idmembresia']);
    
    // Validar que la membresía pertenezca al gimnasio
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM membresias WHERE idmembresia = ? AND idgimnasio = ? AND deleted = 0");
    $stmt->execute([$nueva_membresia_id, $gimnasio_id]);
    if ($stmt->fetchColumn() == 0) {
        $error = "La membresía seleccionada no es válida para este gimnasio";
    } else {
        try {
            // Actualizar la membresía del miembro
            $stmt = $pdo->prepare("UPDATE miembros SET idmembresia = ? WHERE idmiembro = ? AND idgimnasio = ?");
            $stmt->execute([$nueva_membresia_id, $miembro_id, $gimnasio_id]);
            
            $success = "Membresía actualizada correctamente";
            
            // Actualizar datos del miembro y la membresía actual
            $stmt = $pdo->prepare("SELECT * FROM miembros WHERE idmiembro = ? AND idgimnasio = ?");
            $stmt->execute([$miembro_id, $gimnasio_id]);
            $miembro = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT * FROM membresias WHERE idmembresia = ? AND idgimnasio = ?");
            $stmt->execute([$miembro['idmembresia'], $gimnasio_id]);
            $membresia_actual = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Error al actualizar la membresía: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Membresía - Sistema de Gestión de Gimnasio</title>
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
                        <h2>Cambiar Membresía</h2>
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
                    
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Miembro: <?php echo htmlspecialchars($miembro['miembro']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Membresía Actual</h5>
                                    <div class="card mb-3 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($membresia_actual['membresia']); ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title text-primary">$<?php echo $membresia_actual['precio']; ?></h4>
                                            <p class="card-text"><?php echo htmlspecialchars($membresia_actual['descripcion']); ?></p>
                                            <p><strong>Estado:</strong> 
                                                <?php if ($miembro['estado_membresia']): ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactiva</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>Seleccionar Nueva Membresía</h5>
                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $miembro_id); ?>">
                                        <div class="mb-3">
                                            <select class="form-select" id="idmembresia" name="idmembresia" required>
                                                <option value="">-- Seleccione una membresía --</option>
                                                <?php foreach ($membresias as $membresia): ?>
                                                    <?php if ($membresia['idmembresia'] != $miembro['idmembresia']): ?>
                                                        <option value="<?php echo $membresia['idmembresia']; ?>">
                                                            <?php echo htmlspecialchars($membresia['membresia']); ?> - $<?php echo $membresia['precio']; ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-exchange-alt"></i> Cambiar Membresía
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Todas las Membresías Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($membresias as $membresia): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 <?php echo ($membresia['idmembresia'] == $miembro['idmembresia']) ? 'border-primary' : ''; ?>">
                                            <div class="card-header <?php echo ($membresia['idmembresia'] == $miembro['idmembresia']) ? 'bg-primary text-white' : 'bg-light'; ?>">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($membresia['membresia']); ?></h6>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title <?php echo ($membresia['idmembresia'] == $miembro['idmembresia']) ? 'text-primary' : ''; ?>">
                                                    $<?php echo $membresia['precio']; ?>
                                                </h4>
                                                <p class="card-text"><?php echo htmlspecialchars($membresia['descripcion']); ?></p>
                                            </div>
                                            <?php if ($membresia['idmembresia'] == $miembro['idmembresia']): ?>
                                                <div class="card-footer bg-light">
                                                    <span class="badge bg-primary">Membresía Actual</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
