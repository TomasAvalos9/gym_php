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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Gimnasio - Sistema de Gestión</title>
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
                            <a class="nav-link active" href="gimnasio_info.php">
                                <i class="fas fa-building"></i> Información del Gimnasio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="membresia.php">
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
                    <h2 class="mb-4">Información del Gimnasio</h2>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">Datos del Gimnasio</h5>
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title"><?php echo htmlspecialchars($gimnasio['gimnasio']); ?></h3>
                                    <hr>
                                    <p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong> <?php echo htmlspecialchars($gimnasio['direccion']); ?></p>
                                    <p><strong><i class="fas fa-phone"></i> Teléfono:</strong> <?php echo htmlspecialchars($gimnasio['telefono']); ?></p>
                                    <p><strong><i class="fas fa-envelope"></i> Correo:</strong> <?php echo htmlspecialchars($gimnasio['correo']); ?></p>
                                    <p><strong><i class="fas fa-map"></i> Localidad:</strong> <?php echo htmlspecialchars($gimnasio['localidad']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Horarios y Estado</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h5><i class="fas fa-clock"></i> Horario de Atención</h5>
                                        <p class="lead"><?php echo htmlspecialchars($gimnasio['horario']); ?></p>
                                    </div>
                                    
                                    <div>
                                        <h5><i class="fas fa-users"></i> Estado Actual</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="display-4 me-3"><?php echo $gimnasio['estado']; ?></div>
                                            <div>personas actualmente en el gimnasio</div>
                                        </div>
                                        
                                        <?php
                                        // Calcular porcentaje de ocupación (asumiendo capacidad máxima de 50 personas)
                                        $capacidad_maxima = 50;
                                        $porcentaje = min(100, ($gimnasio['estado'] / $capacidad_maxima) * 100);
                                        
                                        // Determinar color de la barra de progreso
                                        $color = 'success';
                                        if ($porcentaje > 70) $color = 'warning';
                                        if ($porcentaje > 90) $color = 'danger';
                                        ?>
                                        
                                        <div class="progress mt-2" style="height: 25px;">
                                            <div class="progress-bar bg-<?php echo $color; ?>" role="progressbar" 
                                                style="width: <?php echo $porcentaje; ?>%;" 
                                                aria-valuenow="<?php echo $gimnasio['estado']; ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="<?php echo $capacidad_maxima; ?>">
                                                <?php echo round($porcentaje); ?>%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Ubicación</h5>
                        </div>
                        <div class="card-body">
                            <div class="ratio ratio-16x9">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3284.0168878895474!2d-58.38375908477038!3d-34.60373288045943!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4aa9f0a6da5edb%3A0x11bead4e234e558b!2sObelisco!5e0!3m2!1ses!2sar!4v1624568182261!5m2!1ses!2sar" 
                                    allowfullscreen="" 
                                    loading="lazy">
                                </iframe>
                            </div>
                            <div class="mt-3">
                                <p class="text-muted"><i class="fas fa-info-circle"></i> Mapa de referencia. La ubicación exacta puede variar.</p>
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
