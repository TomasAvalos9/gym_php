<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 1) {
    header("Location: ../index.php");
    exit;
}

$gimnasio_id = $_SESSION['gimnasio_id'];

// --- PROCESAR FORMULARIO DE LOCALIDAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'localidad') {
    $localidad = trim($_POST['localidad']);
    $idlocalidad = intval($_POST['idlocalidad'] ?? 0);

    if (!empty($localidad)) {
        if ($idlocalidad > 0) {
            $stmt = $pdo->prepare("UPDATE Localidades SET localidad=? WHERE idlocalidad=? AND idgimnasio=?");
            $stmt->execute([$localidad, $idlocalidad, $gimnasio_id]);
            header("Location: otros.php?msg=localidad_editada");
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO Localidades(localidad,idgimnasio,deleted) VALUES(?,?,0)");
            $stmt->execute([$localidad, $gimnasio_id]);
            header("Location: otros.php?msg=localidad_agregada");
            exit;
        }
    } else {
        $error_localidad = "El nombre de la localidad es obligatorio.";
    }
}

// --- PROCESAR FORMULARIO DE BARRIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'barrio') {
    $barrio = trim($_POST['barrio']);
    $idbarrio = intval($_POST['idbarrio'] ?? 0);

    if (!empty($barrio)) {
        if ($idbarrio > 0) {
            $stmt = $pdo->prepare("UPDATE Barrios SET barrio=? WHERE idbarrio=? AND idgimnasio=?");
            $stmt->execute([$barrio, $idbarrio, $gimnasio_id]);
            header("Location: otros.php?msg=barrio_editado");
            exit;
        } else {
            $stmt = $pdo->prepare("INSERT INTO Barrios(barrio,idgimnasio,deleted) VALUES(?,?,0)");
            $stmt->execute([$barrio, $gimnasio_id]);
            header("Location: otros.php?msg=barrio_agregado");
            exit;
        }
    } else {
        $error_barrio = "El nombre del barrio es obligatorio.";
    }
}

// --- OBTENER DATOS ---
$stmt_gym = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio=?");
$stmt_gym->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt_gym->fetchColumn();

$stmt_loc = $pdo->prepare("SELECT idlocalidad, localidad FROM localidades WHERE idgimnasio=? AND deleted=0 ORDER BY localidad ASC");
$stmt_loc->execute([$gimnasio_id]);
$localidades = $stmt_loc->fetchAll();

$stmt_bar = $pdo->prepare("SELECT idbarrio, barrio FROM barrios WHERE idgimnasio=? AND deleted=0 ORDER BY barrio ASC");
$stmt_bar->execute([$gimnasio_id]);
$barrios = $stmt_bar->fetchAll();
?>

<?php $pageTitle = "Localidades y Barrios - GymSystem"; include_once("includes/header.php"); ?>
<?php $gimnasio = ["gimnasio"=>$nombre_gimnasio]; include_once("includes/navbar.php"); ?>

<div class="container-fluid">
    <div class="row">
        <?php include_once("includes/sidebar.php"); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content-area">

            <!-- Mensajes de alerta -->
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        $messages = [
                            'localidad_agregada'=>'Localidad agregada correctamente.',
                            'localidad_editada'=>'Localidad actualizada correctamente.',
                            'barrio_agregado'=>'Barrio agregado correctamente.',
                            'barrio_editado'=>'Barrio actualizado correctamente.'
                        ];
                        echo $messages[$_GET['msg']] ?? 'Operación realizada con éxito.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- COLUMNA LOCALIDADES -->
                <div class="col-lg-6 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Localidades</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLocalidad">
                            <i class="fas fa-plus"></i> Nueva
                        </button>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <?php if(count($localidades) > 0): ?>
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr><th>ID</th><th>Nombre</th><th class="text-end">Acciones</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($localidades as $loc): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($loc['idlocalidad']); ?></td>
                                                <td><?= htmlspecialchars($loc['localidad']); ?></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalLocalidad<?= $loc['idlocalidad']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="eliminar_localidad.php?id=<?= $loc['idlocalidad']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No hay localidades registradas.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA BARRIOS -->
                <div class="col-lg-6 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Barrios</h3>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalBarrio">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <?php if(count($barrios) > 0): ?>
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr><th>ID</th><th>Nombre</th><th class="text-end">Acciones</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($barrios as $bar): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($bar['idbarrio']); ?></td>
                                                <td><?= htmlspecialchars($bar['barrio']); ?></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalBarrio<?= $bar['idbarrio']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="eliminar_barrio.php?id=<?= $bar['idbarrio']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No hay barrios registrados.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<!-- INCLUIR MODALES -->
<?php include 'modals/modals_localidad.php'; ?>
<?php include 'modals/modals_barrio.php'; ?>

<?php include_once("includes/footer.php"); ?>
