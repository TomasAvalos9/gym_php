<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php
require_once '../config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["tipo_usuario"] != 1) {
    header("Location: ../index.php");
    exit;
}

$gimnasio_id = $_SESSION["gimnasio_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("Recibida solicitud POST en agregar_pago.php");
    $miembro_id = filter_input(INPUT_POST, 'miembro_id', FILTER_VALIDATE_INT);
    $idmembresia = filter_input(INPUT_POST, 'idmembresia', FILTER_VALIDATE_INT);
    $fecha_pago = filter_input(INPUT_POST, 'fecha_pago', FILTER_SANITIZE_STRING);
    $monto_pagado = filter_input(INPUT_POST, 'monto_pagado', FILTER_VALIDATE_FLOAT);

    error_log("Datos recibidos: miembro_id=" . $miembro_id . ", idmembresia=" . $idmembresia . ", fecha_pago=" . $fecha_pago . ", monto_pagado=" . $monto_pagado);

    if (!$miembro_id || !$idmembresia || !$fecha_pago || !$monto_pagado) {
        error_log("Datos incompletos o inválidos: miembro_id=" . $miembro_id . ", idmembresia=" . $idmembresia . ", fecha_pago=" . $fecha_pago . ", monto_pagado=" . $monto_pagado);
        // Redirigir con mensaje de error
        header("Location: detalle_miembro.php?id=" . $miembro_id . "&error=datos_invalidos");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Insertar el pago
        $stmt = $pdo->prepare("INSERT INTO pagos (idmiembro, idmembresia, fecha_pago, monto_pagado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$miembro_id, $idmembresia, $fecha_pago, $monto_pagado]);
        error_log("Pago insertado. Filas afectadas: " . $stmt->rowCount());

        // Actualizar la fecha de último pago del miembro (asumiendo que hay un campo para esto en la tabla miembros)
        // Si no existe, se debería agregar o ajustar la lógica para determinar la última fecha de pago.
        // Por ahora, actualizaremos el estado de la membresía a activa si no lo está.
        $stmt = $pdo->prepare("UPDATE miembros SET estado_membresia = 1 WHERE idmiembro = ? AND idgimnasio = ?");
        $stmt->execute([$miembro_id, $gimnasio_id]);

        $pdo->commit();
        // Redirigir con mensaje de éxito
        header("Location: detalle_miembro.php?id=" . $miembro_id . "&success=pago_registrado");
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al registrar pago: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        // Redirigir con mensaje de error
        header("Location: detalle_miembro.php?id=" . $miembro_id . "&error=error_db");
    }
    exit;
}

// Si no es una solicitud POST, se muestra el formulario
$miembro_id = filter_input(INPUT_GET, 'miembro_id', FILTER_VALIDATE_INT);

if (!$miembro_id) {
    echo "<p class='text-danger'>ID de miembro no proporcionado.</p>";
    exit;
}

// Obtener las membresías disponibles para el gimnasio
$stmt = $pdo->prepare("SELECT idmembresia, membresia, precio FROM membresias WHERE idgimnasio = ? AND deleted = 0");
$stmt->execute([$gimnasio_id]);
$membresias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el precio de la membresía actual del miembro para pre-rellenar el monto
$stmt = $pdo->prepare("SELECT m.precio FROM miembros mi JOIN membresias m ON mi.idmembresia = m.idmembresia WHERE mi.idmiembro = ?");
$stmt->execute([$miembro_id]);
$precio_membresia_actual = $stmt->fetchColumn();

?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Registrar Nuevo Pago</h5>
        </div>
        <div class="card-body">
            <form id="formAgregarPago" action="agregarPago.php" method="POST">
                <input type="hidden" name="miembro_id" value="<?php echo htmlspecialchars($miembro_id); ?>">
                <div class="mb-3">
                    <label for="idmembresia" class="form-label">Membresía</label>
                    <select class="form-select" id="idmembresia" name="idmembresia" required>
                        <option value="">Seleccione una membresía</option>
                        <?php foreach ($membresias as $membresia): ?>
                            <option value="<?php echo htmlspecialchars($membresia['idmembresia']); ?>" data-precio="<?php echo htmlspecialchars($membresia['precio']); ?>">
                                <?php echo htmlspecialchars($membresia['membresia']); ?> (<?php echo htmlspecialchars($membresia['precio']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="monto_pagado" class="form-label">Monto Pagado</label>
                    <input type="number" step="0.01" class="form-control" id="monto_pagado" name="monto_pagado" value="<?php echo htmlspecialchars($precio_membresia_actual); ?>" required>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="detalle_miembro.php?id=<?php echo $miembro_id; ?>" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("idmembresia").addEventListener("change", function() {
        var selectedOption = this.options[this.selectedIndex];
        var precio = selectedOption.getAttribute("data-precio");
        document.getElementById("monto_pagado").value = precio;
    });
</script>
</body>
</html>