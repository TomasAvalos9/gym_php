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

// Obtener todas las rutinas del gimnasio
$stmt = $pdo->prepare("
    SELECT r.*, d.lunes, d.martes, d.miercoles, d.jueves, d.viernes, d.sabado,
    (SELECT COUNT(*) FROM rutinas_miembro rm WHERE rm.idrutina = r.idrutina) as total_asignaciones
    FROM rutinas r
    JOIN detalles d ON r.iddetalles = d.iddetalles
    WHERE r.idgimnasio = ? AND r.deleted = 0
    ORDER BY r.rutina
");
$stmt->execute([$gimnasio_id]);
$rutinas = $stmt->fetchAll();

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt->fetchColumn();
?>

<?php $pageTitle = "Rutinas - Sistema de Gestión de Gimnasio"; include_once("includes/header.php"); ?>
<?php $gimnasio = ["gimnasio" => $nombre_gimnasio]; include_once("includes/navbar.php"); ?>

    <div class="container-fluid">
        <div class="row">
            <?php include_once("includes/sidebar.php"); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Rutinas</h2>
                        <a href="agregarEditarRutina.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Rutina
                        </a>
                    </div>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'rutina_agregada'): ?>
                        <div class="alert alert-success">Rutina agregada correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'rutina_editada'): ?>
                        <div class="alert alert-success">Rutina actualizada correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'rutina_eliminada'): ?>
                        <div class="alert alert-success">Rutina eliminada correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (count($rutinas) > 0): ?>
                        <div class="row">
                            <?php foreach ($rutinas as $rutina): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-dark text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0"><?php echo htmlspecialchars($rutina['rutina']); ?></h5>
                                                <div>
                                                    <a href="agregarEditarRutina.php?id=<?php echo $rutina['idrutina']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="eliminar_rutina.php?id=<?php echo $rutina['idrutina']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta rutina?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
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
                                            
                                            <div class="mt-3">
                                                <span class="badge bg-info">
                                                    <i class="fas fa-users"></i> Asignada a <?php echo $rutina['total_asignaciones']; ?> miembros
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>No hay rutinas registradas. ¡Comience creando una nueva rutina!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

<?php include_once("includes/footer.php"); ?>