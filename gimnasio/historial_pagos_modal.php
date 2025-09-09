<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 1) {
    echo '<p class="text-danger">Acceso denegado.</p>';
    exit;
}

$gimnasio_id = $_SESSION['gimnasio_id'];
$miembro_id = filter_input(INPUT_GET, 'miembro_id', FILTER_VALIDATE_INT);

if (!$miembro_id) {
    echo '<p class="text-danger">ID de miembro no proporcionado.</p>';
    exit;
}

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

<div class="modal-header bg-info text-white">
    <h5 class="modal-title" id="historialPagosModalLabel">Historial de Pagos</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
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
                            <td><?php echo htmlspecialchars($pago['fecha_pago']); ?></td>
                            <td><?php echo htmlspecialchars($pago['membresia']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($pago['monto_pagado'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center">No hay pagos registrados para este miembro.</p>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>

