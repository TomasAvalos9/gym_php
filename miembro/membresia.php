<?php
// Incluir archivo de configuración
require_once '../config.php';

// Verificar si el usuario está logueado y es tipo miembro
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 2) {
    header("Location: ../index.php");
    exit;
}

// Obtener datos del miembro
$miembro_id = $_SESSION['miembro_id'];
$stmt = $pdo->prepare("SELECT * FROM miembros WHERE idmiembro = ?");
$stmt->execute([$miembro_id]);
$miembro = $stmt->fetch();

// Obtener datos del gimnasio al que pertenece el miembro
$gimnasio_id = $miembro['idgimnasio'];
$stmt = $pdo->prepare("SELECT * FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$gimnasio = $stmt->fetch();

// Obtener datos de la membresía del miembro (solo del gimnasio al que pertenece)
$stmt = $pdo->prepare("
    SELECT m.* 
    FROM membresias m
    JOIN miembros mi ON m.idmembresia = mi.idmembresia
    WHERE mi.idmiembro = ? AND m.idgimnasio = ? AND m.deleted = 0
");
$stmt->execute([$miembro_id, $gimnasio_id]);
$membresia = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Membresía - Sistema de Gestión de Gimnasio</title>
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
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($miembro['miembro']); ?>
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
                            <a class="nav-link" href="rutinas.php">
                                <i class="fas fa-dumbbell"></i> Mis Rutinas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gimnasio_info.php">
                                <i class="fas fa-building"></i> Información del Gimnasio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="membresia.php">
                                <i class="fas fa-id-card"></i> Mi Membresía
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="perfil.php">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid">
                    <h2 class="mb-4">Mi Membresía</h2>
                    
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Detalles de Membresía</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($membresia): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4 border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0"><?php echo htmlspecialchars($membresia['membresia']); ?></h5>
                                            </div>
                                            <div class="card-body">
                                                <h2 class="card-title text-primary">$<?php echo $membresia['precio']; ?></h2>
                                                <p class="card-text"><?php echo htmlspecialchars($membresia['descripcion']); ?></p>
                                                <div class="mt-3">
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
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Información del Gimnasio</h5>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($gimnasio['gimnasio']); ?></h5>
                                                <p class="card-text"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($gimnasio['direccion']); ?></p>
                                                <p class="card-text"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($gimnasio['telefono']); ?></p>
                                                <p class="card-text"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($gimnasio['correo']); ?></p>
                                                <p class="card-text"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($gimnasio['horario']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <p><i class="fas fa-info-circle"></i> Para cambiar tu membresía o resolver cualquier problema relacionado, por favor contacta directamente con el personal del gimnasio.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <p>No tienes una membresía activa actualmente. Por favor, contacta con el personal del gimnasio.</p>
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
