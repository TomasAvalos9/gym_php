<?php
// Este archivo ahora solo procesará la solicitud POST y devolverá JSON
// El formulario HTML y el JavaScript se han movido a agregar_pago.php

require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$gimnasio_id = $_SESSION['gimnasio_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("Recibida solicitud POST en agregar_pago_modal.php");
    $miembro_id = filter_input(INPUT_POST, 'miembro_id', FILTER_VALIDATE_INT);
    $idmembresia = filter_input(INPUT_POST, 'idmembresia', FILTER_VALIDATE_INT);
    $fecha_pago = filter_input(INPUT_POST, 'fecha_pago', FILTER_SANITIZE_STRING);
    $monto_pagado = filter_input(INPUT_POST, 'monto_pagado', FILTER_VALIDATE_FLOAT);

    error_log("Datos recibidos: miembro_id=" . $miembro_id . ", idmembresia=" . $idmembresia . ", fecha_pago=" . $fecha_pago . ", monto_pagado=" . $monto_pagado);

    if (!$miembro_id || !$idmembresia || !$fecha_pago || !$monto_pagado) {
        error_log("Datos incompletos o inválidos: miembro_id=" . $miembro_id . ", idmembresia=" . $idmembresia . ", fecha_pago=" . $fecha_pago . ", monto_pagado=" . $monto_pagado);
        echo json_encode(["success" => false, "message" => "Datos incompletos o inválidos."]);
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
        echo json_encode(['success' => true, 'message' => 'Pago registrado exitosamente.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al registrar pago: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()]);
    }
    exit;
}

// Si no es una solicitud POST, se muestra un mensaje de error o se redirige
echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
exit;

?>