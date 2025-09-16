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
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idmiembro = ? AND idgimnasio = ? AND deleted = 0");
    $stmt->execute([$miembro_id, $gimnasio_id]);
    if ($stmt->fetchColumn() == 0) {
        header("Location: miembros.php");
        exit;
    }

    // Obtener datos del miembro
    $stmt = $pdo->prepare("
        SELECT m.*, b.barrio, l.localidad, mem.membresia, mem.precio 
        FROM miembros m
        JOIN barrios b ON m.idbarrio = b.idbarrio
        JOIN localidades l ON m.idlocalidad = l.idlocalidad
        JOIN membresias mem ON m.idmembresia = mem.idmembresia
        WHERE m.idmiembro = ? AND m.idgimnasio = ?
    ");
    $stmt->execute([$miembro_id, $gimnasio_id]);
    $miembro = $stmt->fetch();

    // Obtener rutinas asignadas al miembro
    $stmt = $pdo->prepare("
        SELECT r.*, d.* 
        FROM rutinas r
        JOIN rutinas_miembro rm ON r.idrutina = rm.idrutina
        JOIN detalles d ON r.iddetalles = d.iddetalles
        WHERE rm.idmiembro = ? AND r.idgimnasio = ? AND r.deleted = 0
    ");
    $stmt->execute([$miembro_id, $gimnasio_id]);
    $rutinas = $stmt->fetchAll();

    function calcularDeuda($fechaUltimoPago, $precioMensual) {
        if (!$fechaUltimoPago) return ['meses' => 0, 'deuda' => 0];

        $fechaPago = new DateTime($fechaUltimoPago);
        $hoy = new DateTime();

        $meses = ($hoy->format('Y') - $fechaPago->format('Y')) * 12 + ($hoy->format('m') - $fechaPago->format('m'));

        if ($meses < 0) $meses = 0;

        $deuda = $meses * $precioMensual;

        return ['meses' => $meses, 'deuda' => $deuda];
    }

    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalle de Miembro - Sistema de Gestión de Gimnasio</title>
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
                            <h2>Detalle de Miembro</h2>
                            <div>
                                <a href="miembros.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                                <a href="agregarEditarMiembro.php?id=<?php echo $miembro_id; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Información Personal</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <?php if (!empty($miembro['foto'])): ?>
                                                <img src="../<?php echo htmlspecialchars($miembro['foto']); ?>" 
                                                    alt="Foto de <?php echo htmlspecialchars($miembro['miembro']); ?>" 
                                                    class="rounded-circle shadow" width="120" height="120" 
                                                    style="object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="card-title text-center mb-4"><?php echo htmlspecialchars($miembro['miembro']); ?></h3>
                                        
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">DNI:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($miembro['dni']); ?></div>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Teléfono:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($miembro['telefono']); ?></div>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Email:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($miembro['correo']); ?></div>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Barrio:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($miembro['barrio']); ?></div>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Localidad:</div>
                                            <div class="col-md-8"><?php echo htmlspecialchars($miembro['localidad']); ?></div>
                                        </div>
                                        
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Estado:</div>
                                            <div class="col-md-8">
                                                <?php if ($miembro['estado']): ?>
                                                    <span class="badge bg-success">En el gimnasio</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Ausente</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <!-- 🔹 Botón Historial de Pagos -->
                                        <div class="d-grid gap-2 mt-4">
                                            <a href="agregarPago.php?miembro_id=<?php echo $miembro_id; ?>" class="btn btn-success">
                                                <i class="fas fa-dollar-sign"></i> Agregar Pago
                                            </a>
                                            <a href="historialPago.php?miembro_id=<?php echo $miembro_id; ?>" class="btn btn-info">
                                                <i class="fas fa-receipt"></i> Ver Historial de Pagos
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Membresía</h5>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title"><?php echo htmlspecialchars($miembro['membresia']); ?></h4>
                                        
                                        <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Estado:</div>
                                        <div class="col-md-8">
                                            <?php if ($miembro['estado_membresia']): ?>
                                                <span class="badge bg-success">Activa</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactiva</span>
                                            <?php endif; ?>
                                        </div>

                                    

                                        
                                        <div class="mt-4">
                                            <h5>Acciones</h5>
                                            <div class="d-grid gap-2">
                                                <a href="cambiar_membresia.php?id=<?php echo $miembro_id; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-exchange-alt"></i> Cambiar Membresía
                                                </a>
                                                <?php if ($miembro['estado_membresia']): ?>
                                                    <a href="toggle_membresia.php?id=<?php echo $miembro_id; ?>&estado=0" class="btn btn-outline-danger">
                                                        <i class="fas fa-times-circle"></i> Desactivar Membresía
                                                    </a>
                                                <?php else: ?>
                                                    <a href="toggle_membresia.php?id=<?php echo $miembro_id; ?>&estado=1" class="btn btn-outline-success">
                                                        <i class="fas fa-check-circle"></i> Activar Membresía
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mt-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Estado de Asistencia</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <?php if ($miembro['estado']): ?>
                                                <div class="display-6 text-success">
                                                    <i class="fas fa-check-circle"></i> Presente
                                                </div>
                                            <?php else: ?>
                                                <div class="display-6 text-secondary">
                                                    <i class="fas fa-times-circle"></i> Ausente
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <?php if ($miembro['estado']): ?>
                                                <a href="toggle_estado.php?id=<?php echo $miembro_id; ?>&estado=0" class="btn btn-outline-secondary">
                                                    <i class="fas fa-sign-out-alt"></i> Marcar como Ausente
                                                </a>
                                            <?php else: ?>
                                                <a href="toggle_estado.php?id=<?php echo $miembro_id; ?>&estado=1" class="btn btn-outline-success">
                                                    <i class="fas fa-sign-in-alt"></i> Marcar como Presente
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Rutinas Asignadas</h5>
                                    <a href="asignar_rutina.php?miembro_id=<?php echo $miembro_id; ?>" class="btn btn-sm btn-light">
                                        <i class="fas fa-plus"></i> Asignar Rutina
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($rutinas) > 0): ?>
                                    <?php foreach ($rutinas as $rutina): ?>
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($rutina['rutina']); ?></h6>
                                                <a href="desasignar_rutina.php?miembro_id=<?php echo $miembro_id; ?>&rutina_id=<?php echo $rutina['idrutina']; ?>" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Desasignar
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Lunes</th>
                                                            <th>Martes</th>
                                                            <th>Miércoles</th>
                                                            <th>Jueves</th>
                                                            <th>Viernes</th>
                                                            <th>Sábado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><?php echo !empty($rutina['lunes']) ? htmlspecialchars($rutina['lunes']) : '-'; ?></td>
                                                            <td><?php echo !empty($rutina['martes']) ? htmlspecialchars($rutina['martes']) : '-'; ?></td>
                                                            <td><?php echo !empty($rutina['miercoles']) ? htmlspecialchars($rutina['miercoles']) : '-'; ?></td>
                                                            <td><?php echo !empty($rutina['jueves']) ? htmlspecialchars($rutina['jueves']) : '-'; ?></td>
                                                            <td><?php echo !empty($rutina['viernes']) ? htmlspecialchars($rutina['viernes']) : '-'; ?></td>
                                                            <td><?php echo !empty($rutina['sabado']) ? htmlspecialchars($rutina['sabado']) : '-'; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p>Este miembro no tiene rutinas asignadas actualmente.</p>
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
