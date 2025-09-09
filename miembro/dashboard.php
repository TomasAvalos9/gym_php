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
$stmt = $pdo->prepare("SELECT m.*, b.barrio, l.localidad, mem.membresia 
                      FROM miembros m
                      JOIN barrios b ON m.idbarrio = b.idbarrio
                      JOIN localidades l ON m.idlocalidad = l.idlocalidad
                      JOIN membresias mem ON m.idmembresia = mem.idmembresia
                      WHERE m.idmiembro = ?");
$stmt->execute([$miembro_id]);
$miembro = $stmt->fetch();

// Obtener datos del gimnasio al que pertenece el miembro
$gimnasio_id = $miembro['idgimnasio'];
$stmt = $pdo->prepare("SELECT * FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$gimnasio = $stmt->fetch();

// Contar rutinas asignadas al miembro
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM rutinas_miembro rm
    JOIN rutinas r ON rm.idrutina = r.idrutina
    WHERE rm.idmiembro = ? AND r.idgimnasio = ? AND r.deleted = 0
");
$stmt->execute([$miembro_id, $gimnasio_id]);
$total_rutinas = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Miembro - Sistema de Gestión de Gimnasio</title>
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
                            <a class="nav-link active" href="dashboard.php">
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
                    <h2 class="mb-4">Dashboard de Miembro</h2>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Bienvenido</h5>
                                    <h2 class="display-4"><?php echo htmlspecialchars($miembro['miembro']); ?></h2>
                                    <p>Miembro de <?php echo htmlspecialchars($gimnasio['gimnasio']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Estado del Gimnasio</h5>
                                    <h2 class="display-4"><?php echo $gimnasio['estado']; ?></h2>
                                    <p>Personas actualmente en el gimnasio</p>
                                    <div class="progress mt-3" style="height: 10px;">
                                        <?php
                                        // Calcular porcentaje de ocupación (asumiendo capacidad máxima de 50 personas)
                                        $capacidad_maxima = 50;
                                        $porcentaje = min(100, ($gimnasio['estado'] / $capacidad_maxima) * 100);
                                        ?>
                                        <div class="progress-bar bg-light" role="progressbar" 
                                            style="width: <?php echo $porcentaje; ?>%;" 
                                            aria-valuenow="<?php echo $gimnasio['estado']; ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="<?php echo $capacidad_maxima; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><?php echo $total_rutinas; ?></h1>
                                    <h5>Rutinas Asignadas</h5>
                                    <a href="rutinas.php" class="btn btn-light mt-3">Ver Rutinas</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><?php echo htmlspecialchars($miembro['membresia']); ?></h1>
                                    <h5>Mi Membresía</h5>
                                    <a href="membresia.php" class="btn btn-light mt-3">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><i class="fas fa-building"></i></h1>
                                    <h5>Información del Gimnasio</h5>
                                    <a href="gimnasio_info.php" class="btn btn-light mt-3">Ver Información</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">Mi Perfil</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($miembro['miembro']); ?></p>
                                    <p><strong>DNI:</strong> <?php echo htmlspecialchars($miembro['dni']); ?></p>
                                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($miembro['telefono']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($miembro['gmail']); ?></p>
                                    <p><strong>Barrio:</strong> <?php echo htmlspecialchars($miembro['barrio']); ?></p>
                                    <p><strong>Localidad:</strong> <?php echo htmlspecialchars($miembro['localidad']); ?></p>
                                    <a href="perfil.php" class="btn btn-primary">Editar Perfil</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">Horario del Gimnasio</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Gimnasio:</strong> <?php echo htmlspecialchars($gimnasio['gimnasio']); ?></p>
                                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($gimnasio['direccion']); ?></p>
                                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($gimnasio['telefono']); ?></p>
                                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($gimnasio['correo']); ?></p>
                                    <p><strong>Horario:</strong> <?php echo htmlspecialchars($gimnasio['horario']); ?></p>
                                </div>
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
