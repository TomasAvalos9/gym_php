<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos</title>
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
$miembro_id = filter_input(INPUT_GET, 'miembro_id', FILTER_VALIDATE_INT);

if (!$miembro_id) {
    echo '<div class="container mt-5"><p class="text-danger">ID de miembro no proporcionado.</p></div>';
    exit;
}

// Obtener datos del miembro para mostrar en el título
$stmt_miembro = $pdo->prepare("SELECT miembro FROM miembros WHERE idmiembro = ? AND idgimnasio = ?");
$stmt_miembro->execute([$miembro_id, $gimnasio_id]);
$nombre_miembro = $stmt_miembro->fetchColumn();

// Obtener historial de pagos del miembro
$stmt = $pdo->prepare("
    SELECT p.fecha_pago, p.monto_pagado, m.membresia 
    FROM pagos p
    JOIN membresias m ON p.idmembresia = m.idmembresia
    WHERE p.idmiembro = ? AND m.idgimnasio = ? AND p.deleted = 0
    ORDER BY p.fecha_pago DESC
");
$stmt->execute([$miembro_id, $gimnasio_id]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Historial de Pagos de <?php echo htmlspecialchars($nombre_miembro); ?></h5>
        </div>
        <div class="card-body">
            <?php if (count($pagos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha de Pago</th>
                                <th>Membresía</th>
                                <th>Monto Pagado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pago["fecha_pago"]); ?></td>
                                    <td><?php echo htmlspecialchars($pago["membresia"]); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($pago["monto_pagado"], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No hay pagos registrados para este miembro.</p>
            <?php endif; ?>
        </div>
        <div class="card-footer text-end">
            <a href="detalle_miembro.php?id=<?php echo $miembro_id; ?>" class="btn btn-secondary">Volver al Detalle del Miembro</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



