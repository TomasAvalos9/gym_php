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

// Inicializar variables
$error = '';
$success = '';
$rutina = $lunes = $martes = $miercoles = $jueves = $viernes = $sabado = '';
$editando = false;
$idrutina = null;
$iddetalles = null;

// Si viene un idrutina por GET, es edición
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idrutina = intval($_GET['id']);
    $editando = true;

    // Obtener datos de la rutina y sus detalles
    $stmt = $pdo->prepare("
        SELECT r.rutina, d.iddetalles, d.lunes, d.martes, d.miercoles, d.jueves, d.viernes, d.sabado 
        FROM rutinas r
        JOIN detalles d ON r.iddetalles = d.iddetalles
        WHERE r.idrutina = ? AND r.idgimnasio = ? AND r.deleted = 0
    ");
    $stmt->execute([$idrutina, $gimnasio_id]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        $rutina = $datos['rutina'];
        $lunes = $datos['lunes'];
        $martes = $datos['martes'];
        $miercoles = $datos['miercoles'];
        $jueves = $datos['jueves'];
        $viernes = $datos['viernes'];
        $sabado = $datos['sabado'];
        $iddetalles = $datos['iddetalles'];
    } else {
        // Si no existe la rutina, volver
        header("Location: rutinas.php");
        exit;
    }
}

// Procesar el formulario si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rutina = limpiarDato($_POST['rutina']);
    $lunes = limpiarDato($_POST['lunes']);
    $martes = limpiarDato($_POST['martes']);
    $miercoles = limpiarDato($_POST['miercoles']);
    $jueves = limpiarDato($_POST['jueves']);
    $viernes = limpiarDato($_POST['viernes']);
    $sabado = limpiarDato($_POST['sabado']);

    // Revisar si es edición por POST
    if (!empty($_POST['idrutina']) && !empty($_POST['iddetalles'])) {
        $editando = true;
        $idrutina = intval($_POST['idrutina']);
        $iddetalles = intval($_POST['iddetalles']);
    }

    if (empty($rutina)) {
        $error = "El nombre de la rutina es obligatorio";
    } else {
        try {
            $pdo->beginTransaction();

            if ($editando) {
                // UPDATE detalles
                $stmt = $pdo->prepare("
                    UPDATE detalles 
                    SET lunes = ?, martes = ?, miercoles = ?, jueves = ?, viernes = ?, sabado = ?
                    WHERE iddetalles = ?
                ");
                $stmt->execute([$lunes, $martes, $miercoles, $jueves, $viernes, $sabado, $iddetalles]);

                // UPDATE rutina
                $stmt = $pdo->prepare("UPDATE rutinas SET rutina = ? WHERE idrutina = ? AND idgimnasio = ?");
                $stmt->execute([$rutina, $idrutina, $gimnasio_id]);

                $success = "Rutina actualizada correctamente";
            } else {
                // INSERT detalles
                $stmt = $pdo->prepare("
                    INSERT INTO detalles (lunes, martes, miercoles, jueves, viernes, sabado, deleted) 
                    VALUES (?, ?, ?, ?, ?, ?, 0)
                ");
                $stmt->execute([$lunes, $martes, $miercoles, $jueves, $viernes, $sabado]);
                $iddetalles = $pdo->lastInsertId();

                // INSERT rutina
                $stmt = $pdo->prepare("
                    INSERT INTO rutinas (rutina, iddetalles, idgimnasio, deleted) 
                    VALUES (?, ?, ?, 0)
                ");
                $stmt->execute([$rutina, $iddetalles, $gimnasio_id]);

                $success = "Rutina agregada correctamente";

                // Limpiar campos
                $rutina = $lunes = $martes = $miercoles = $jueves = $viernes = $sabado = '';
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al guardar la rutina: " . $e->getMessage();
        }
    }
}

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editando ? 'Editar' : 'Agregar'; ?> Rutina - Sistema de Gestión de Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding-top: 60px; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 56px; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0,0,0,.1); background-color: #343a40; }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 48px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .nav-link { color: #ced4da; font-weight: 500; padding: .5rem 1rem; }
        .nav-link:hover { color: #fff; }
        .nav-link.active { color: #fff; background-color: #495057; }
        .nav-link i { margin-right: 10px; }
        .main-content { margin-left: 240px; padding: 20px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; height: auto; padding: 0; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
<div class="container-fluid">
<a class="navbar-brand" href="#">GymSystem</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
<i class="fas fa-dumbbell"></i> <?php echo htmlspecialchars($nombre_gimnasio); ?>
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
<li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="miembros.php"><i class="fas fa-users"></i> Miembros</a></li>
<li class="nav-item"><a class="nav-link active" href="rutinas.php"><i class="fas fa-dumbbell"></i> Rutinas</a></li>
<li class="nav-item"><a class="nav-link" href="membresias.php"><i class="fas fa-id-card"></i> Membresías</a></li>
</ul>
</div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-4">
<h2><?php echo $editando ? 'Editar' : 'Agregar'; ?> Rutina</h2>
<a href="rutinas.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
<div class="card-header bg-dark text-white">
<h5 class="mb-0">Formulario de Rutina</h5>
</div>
<div class="card-body">
<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . ($editando ? "?id=".$idrutina : ""); ?>">
<?php if($editando): ?>
<input type="hidden" name="idrutina" value="<?php echo $idrutina; ?>">
<input type="hidden" name="iddetalles" value="<?php echo $iddetalles; ?>">
<?php endif; ?>

<div class="mb-3">
<label for="rutina" class="form-label">Nombre de la Rutina</label>
<input type="text" class="form-control" id="rutina" name="rutina" value="<?php echo htmlspecialchars($rutina); ?>" required>
</div>

<div class="row mb-3">
<div class="col-md-6"><label for="lunes" class="form-label">Lunes</label><textarea class="form-control" id="lunes" name="lunes" rows="3"><?php echo htmlspecialchars($lunes); ?></textarea></div>
<div class="col-md-6"><label for="martes" class="form-label">Martes</label><textarea class="form-control" id="martes" name="martes" rows="3"><?php echo htmlspecialchars($martes); ?></textarea></div>
</div>

<div class="row mb-3">
<div class="col-md-6"><label for="miercoles" class="form-label">Miércoles</label><textarea class="form-control" id="miercoles" name="miercoles" rows="3"><?php echo htmlspecialchars($miercoles); ?></textarea></div>
<div class="col-md-6"><label for="jueves" class="form-label">Jueves</label><textarea class="form-control" id="jueves" name="jueves" rows="3"><?php echo htmlspecialchars($jueves); ?></textarea></div>
</div>

<div class="row mb-3">
<div class="col-md-6"><label for="viernes" class="form-label">Viernes</label><textarea class="form-control" id="viernes" name="viernes" rows="3"><?php echo htmlspecialchars($viernes); ?></textarea></div>
<div class="col-md-6"><label for="sabado" class="form-label">Sábado</label><textarea class="form-control" id="sabado" name="sabado" rows="3"><?php echo htmlspecialchars($sabado); ?></textarea></div>
</div>

<div class="d-grid gap-2 d-md-flex justify-content-md-end">
<button type="reset" class="btn btn-secondary me-md-2">Limpiar</button>
<button type="submit" class="btn btn-primary"><?php echo $editando ? 'Actualizar' : 'Guardar'; ?> Rutina</button>
</div>
</form>
</div>
</div>
</div>
</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
