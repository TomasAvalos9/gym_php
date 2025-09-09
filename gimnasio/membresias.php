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

// Obtener todas las membresías del gimnasio
$stmt = $pdo->prepare("
    SELECT m.*, 
    (SELECT COUNT(*) FROM miembros mi WHERE mi.idmembresia = m.idmembresia AND mi.idgimnasio = ? AND mi.deleted = 0) as total_miembros
    FROM membresias m
    WHERE m.idgimnasio = ? AND m.deleted = 0
    ORDER BY m.membresia
");
$stmt->execute([$gimnasio_id, $gimnasio_id]);
$membresias = $stmt->fetchAll();

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt->fetchColumn();
?>

<?php $pageTitle = "Membresías - Sistema de Gestión de Gimnasio"; include_once("includes/header.php"); ?>
<?php $gimnasio = ['gimnasio' => $nombre_gimnasio]; include_once("includes/navbar.php"); ?>

    <div class="container-fluid">
        <div class="row">
            <?php include_once("includes/sidebar.php"); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Membresías</h2>
                        <a href="agregarEditarMembresia.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Membresía
                        </a>
                    </div>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'membresia_agregada'): ?>
                        <div class="alert alert-success">Membresía agregada correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'membresia_editada'): ?>
                        <div class="alert alert-success">Membresía actualizada correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'membresia_eliminada'): ?>
                        <div class="alert alert-success">Membresía eliminada correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (count($membresias) > 0): ?>
                        <div class="row">
                            <?php foreach ($membresias as $membresia): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0"><?php echo htmlspecialchars($membresia['membresia']); ?></h5>
                                                <div>
                                                    <a href="agregarEditarMembresia.php?id=<?php echo $membresia['idmembresia']; ?>" class="btn btn-sm btn-light">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($membresia['total_miembros'] == 0): ?>
                                                        <a href="eliminar_membresia.php?id=<?php echo $membresia['idmembresia']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta membresía?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h2 class="card-title text-primary">$<?php echo $membresia['precio']; ?></h2>
                                            <p class="card-text"><?php echo htmlspecialchars($membresia['descripcion']); ?></p>
                                            
                                            <div class="mt-3">
                                                <span class="badge bg-info">
                                                    <i class="fas fa-users"></i> <?php echo $membresia['total_miembros']; ?> miembros
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light">
                                            <div class="d-grid">
                                                <a href="miembros.php?membresia=<?php echo $membresia['idmembresia']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver Miembros
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>No hay membresías registradas. ¡Comience creando una nueva membresía!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

<?php include_once("<includes/footer.php"); ?>