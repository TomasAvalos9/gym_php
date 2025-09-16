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

// --- Buscar por nombre ---
$where = "WHERE m.idgimnasio = ? AND m.deleted = 0";
$params = [$gimnasio_id];

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $nombre = "%" . $_GET['nombre'] . "%";
    $where .= " AND m.miembro LIKE ?";
    $params[] = $nombre;
}

// Obtener todos los miembros (con filtro si aplica)
$stmt = $pdo->prepare("
    SELECT m.*, b.barrio, l.localidad, mem.membresia 
    FROM miembros m
    JOIN barrios b ON m.idbarrio = b.idbarrio
    JOIN localidades l ON m.idlocalidad = l.idlocalidad
    JOIN membresias mem ON m.idmembresia = mem.idmembresia
    $where
    ORDER BY m.miembro
");
$stmt->execute($params);
$miembros = $stmt->fetchAll();

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt->fetchColumn();
?>


<?php $pageTitle = "Miembros - Sistema de Gestión de Gimnasio"; include_once("includes/header.php"); ?>
<?php $gimnasio = ["gimnasio" => $nombre_gimnasio]; include_once("includes/navbar.php"); ?>

    <div class="container-fluid">
        <div class="row">
            <?php include_once("includes/sidebar.php"); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>
                            <?php if (isset($filtro_membresia) && !empty($filtro_membresia) && isset($nombre_membresia)): ?>
                                Miembros con Membresía: <?php echo htmlspecialchars($nombre_membresia); ?>
                            <?php else: ?>
                                Miembros
                            <?php endif; ?>
                        </h2>
                        <a href="agregareditarMiembro.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Miembro
                        </a>
                    </div>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'miembro_agregado'): ?>
                        <div class="alert alert-success">Miembro agregado correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'miembro_editado'): ?>
                        <div class="alert alert-success">Miembro actualizado correctamente</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'miembro_eliminado'): ?>
                        <div class="alert alert-success">Miembro eliminado correctamente</div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Buscar Miembro</h5>
                                </div>
                                <div class="col-md-6">
                                    <form method="GET" action="miembros.php" class="d-flex">
                                        <input type="text" class="form-control me-2" name="nombre" placeholder="Ingrese nombre..." value="<?php echo isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : ''; ?>">
                                        <button type="submit" class="btn btn-primary">Buscar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($miembros) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nombre</th>
                                        <th>DNI</th>
                                        <th>Teléfono</th>
                                        <th>Membresía</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($miembros as $miembro): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($miembro['foto'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($miembro['foto']); ?>" 
                                                        alt="Foto" 
                                                        width="50" height="50" 
                                                        class="rounded-circle shadow-sm" 
                                                        style="object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($miembro['miembro']); ?></td>
                                            <td><?php echo htmlspecialchars($miembro['dni']); ?></td>
                                            <td><?php echo htmlspecialchars($miembro['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($miembro['membresia']); ?></td>
                                            <td>
                                                <?php if ($miembro['estado']): ?>
                                                    <span class="badge bg-success">Presente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Ausente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="detalle_miembro.php?id=<?php echo $miembro['idmiembro']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="agregareditarMiembro.php?id=<?php echo $miembro['idmiembro']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar_miembro.php?id=<?php echo $miembro['idmiembro']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este miembro?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>No hay miembros registrados<?php echo (isset($filtro_membresia) && !empty($filtro_membresia)) ? ' con esta membresía' : ''; ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

<?php include_once("includes/footer.php"); ?>