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

// Procesar el formulario si se ha enviado
$error = '';
$success = '';

// Si viene por GET con ?id= significa que vamos a EDITAR
$membresia_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si es edición, traer los datos de la membresía
if ($membresia_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM membresias WHERE idmembresia = ? AND idgimnasio = ? AND deleted = 0");
    $stmt->execute([$membresia_id, $gimnasio_id]);
    $membresia_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$membresia_existente) {
        $error = "Membresía no encontrada o no pertenece a tu gimnasio.";
        $membresia_id = 0; // lo dejo en 0 para que no intente editar
    }
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $membresia = limpiarDato($_POST['membresia']);
    $precio = floatval($_POST['precio']);
    $descripcion = limpiarDato($_POST['descripcion']);
    $membresia_id_post = isset($_POST['idmembresia']) ? intval($_POST['idmembresia']) : 0;

    if (empty($membresia) || $precio <= 0) {
        $error = "Todos los campos son obligatorios y el precio debe ser mayor a 0";
    } else {
        try {
            if ($membresia_id_post > 0) {
                // UPDATE
                $stmt = $pdo->prepare("
                    UPDATE membresias 
                    SET membresia = ?, precio = ?, descripcion = ? 
                    WHERE idmembresia = ? AND idgimnasio = ?
                ");
                $stmt->execute([$membresia, $precio, $descripcion, $membresia_id_post, $gimnasio_id]);
                $success = "Membresía actualizada correctamente";
            } else {
                // INSERT
                $stmt = $pdo->prepare("
                    INSERT INTO membresias (membresia, precio, descripcion, idgimnasio, deleted) 
                    VALUES (?, ?, ?, ?, 0)
                ");
                $stmt->execute([$membresia, $precio, $descripcion, $gimnasio_id]);
                $success = "Membresía agregada correctamente";
            }

            // Limpiar el formulario después de guardar
            $membresia = '';
            $precio = '';
            $descripcion = '';
            $membresia_id = 0;

        } catch (PDOException $e) {
            $error = "Error al guardar la membresía: " . $e->getMessage();
        }
    }
}

// Obtener datos del gimnasio
$stmt = $pdo->prepare("SELECT gimnasio FROM gimnasio WHERE idgimnasio = ?");
$stmt->execute([$gimnasio_id]);
$nombre_gimnasio = $stmt->fetchColumn();

// Si es edición, cargar datos existentes al formulario
if ($membresia_id > 0 && isset($membresia_existente)) {
    $membresia = $membresia_existente['membresia'];
    $precio = $membresia_existente['precio'];
    $descripcion = $membresia_existente['descripcion'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $membresia_id > 0 ? "Editar Membresía" : "Agregar Membresía"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo $membresia_id > 0 ? "Editar Membresía" : "Agregar Nueva Membresía"; ?></h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="idmembresia" value="<?php echo $membresia_id; ?>">
            
            <div class="mb-3">
                <label class="form-label">Nombre de la Membresía</label>
                <input type="text" name="membresia" class="form-control" 
                       value="<?php echo isset($membresia) ? htmlspecialchars($membresia) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Precio</label>
                <input type="number" step="0.01" min="0" name="precio" class="form-control" 
                       value="<?php echo isset($precio) ? htmlspecialchars($precio) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4"><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $membresia_id > 0 ? "Actualizar" : "Guardar"; ?>
            </button>
            <a href="membresias.php" class="btn btn-secondary">Volver</a>
        </form>
    </div>
</body>
</html>

