<?php
require_once '../config.php';


// Verificar si el usuario está logueado y es tipo gimnasio
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 1) {
    header("Location: ../index.php");
    exit;
}

$gimnasio_id = $_SESSION['gimnasio_id'];

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT * FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$gimnasio = $stmt->fetch();

// Contar miembros
$stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idgimnasio = ? AND deleted = 0");
$stmt->execute([$gimnasio_id]);
$total_miembros = $stmt->fetchColumn();

// Contar miembros activos
$stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idgimnasio = ? AND estado = 1 AND deleted = 0");
$stmt->execute([$gimnasio_id]);
$miembros_activos = $stmt->fetchColumn();

// Contar rutinas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas WHERE idgimnasio = ? AND deleted = 0");
$stmt->execute([$gimnasio_id]);
$total_rutinas = $stmt->fetchColumn();

// Contar membresías
$stmt = $pdo->prepare("SELECT COUNT(*) FROM membresias WHERE idgimnasio = ? AND deleted = 0");
$stmt->execute([$gimnasio_id]);
$total_membresias = $stmt->fetchColumn();

// Últimos 5 miembros
$stmt = $pdo->prepare("
    SELECT m.*, b.barrio, l.localidad, mem.membresia 
    FROM miembros m
    JOIN barrios b ON m.idbarrio = b.idbarrio
    JOIN localidades l ON m.idlocalidad = l.idlocalidad
    JOIN membresias mem ON m.idmembresia = mem.idmembresia
    WHERE m.idgimnasio = ? AND m.deleted = 0
    ORDER BY m.idmiembro DESC
    LIMIT 5
");
$stmt->execute([$gimnasio_id]);
$ultimos_miembros = $stmt->fetchAll();

// Definir título
$pageTitle = "Dashboard - GymSystem";

// Includes
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            
            <!-- Encabezado -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="actualizar_estado.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync"></i> Actualizar Estado
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Tarjetas estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3 mb-4">
                    <div class="card stat-card bg-primary text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Miembros</h6>
                                <h2 class="display-4"><?= $total_miembros ?></h2>
                                <a href="miembros.php" class="text-white">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card stat-card bg-success text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Miembros Activos</h6>
                                <h2 class="display-4"><?= $miembros_activos ?></h2>
                                <p class="mb-0">Actualmente en el gimnasio</p>
                            </div>
                            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card stat-card bg-info text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Rutinas</h6>
                                <h2 class="display-4"><?= $total_rutinas ?></h2>
                                <a href="rutinas.php" class="text-white">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card stat-card bg-warning text-dark h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Membresías</h6>
                                <h2 class="display-4"><?= $total_membresias ?></h2>
                                <a href="membresias.php" class="text-dark">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="stat-icon"><i class="fas fa-id-card"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Últimos miembros -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Últimos Miembros Registrados</h5>
                            <a href="agregar_miembro.php" class="btn btn-sm btn-light">
                                <i class="fas fa-plus"></i> Nuevo Miembro
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Membresía</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($ultimos_miembros): ?>
                                            <?php foreach ($ultimos_miembros as $miembro): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($miembro['miembro']) ?></td>
                                                    <td><?= htmlspecialchars($miembro['membresia']) ?></td>
                                                    <td>
                                                        <?php if ($miembro['estado']): ?>
                                                            <span class="badge bg-success">Presente</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Ausente</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="detalle_miembro.php?id=<?= $miembro['idmiembro'] ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No hay miembros registrados</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($ultimos_miembros): ?>
                                <div class="text-end mt-3">
                                    <a href="miembros.php" class="btn btn-outline-primary">Ver todos los miembros</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Info del gimnasio -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Información del Gimnasio</h5>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title"><?= htmlspecialchars($gimnasio['gimnasio']) ?></h4>
                            <hr>
                            <p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong> <?= htmlspecialchars($gimnasio['direccion']) ?></p>
                            <p><strong><i class="fas fa-phone"></i> Teléfono:</strong> <?= htmlspecialchars($gimnasio['telefono']) ?></p>
                            <p><strong><i class="fas fa-envelope"></i> Correo:</strong> <?= htmlspecialchars($gimnasio['correo']) ?></p>
                            <p><strong><i class="fas fa-map"></i> Localidad:</strong> <?= htmlspecialchars($gimnasio['localidad']) ?></p>
                            <p><strong><i class="fas fa-clock"></i> Horario:</strong> <?= htmlspecialchars($gimnasio['horario']) ?></p>
                            
                            <div class="mt-4">
                                <h5><i class="fas fa-users"></i> Estado Actual</h5>
                                <div class="d-flex align-items-center">
                                    <div class="display-4 me-3"><?= $gimnasio['estado'] ?></div>
                                    <div>personas actualmente en el gimnasio</div>
                                </div>
                                
                                <?php
                                $capacidad_maxima = 50;
                                $porcentaje = min(100, ($gimnasio['estado'] / $capacidad_maxima) * 100);
                                $color = 'success';
                                if ($porcentaje > 70) $color = 'warning';
                                if ($porcentaje > 90) $color = 'danger';
                                ?>
                                
                                <div class="progress mt-2" style="height: 25px;">
                                    <div class="progress-bar bg-<?= $color ?>" role="progressbar" 
                                         style="width: <?= $porcentaje ?>%;" 
                                         aria-valuenow="<?= $gimnasio['estado'] ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="<?= $capacidad_maxima ?>">
                                        <?= round($porcentaje) ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
