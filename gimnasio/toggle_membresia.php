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

// Verificar si se ha proporcionado un ID de miembro
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: miembros.php");
    exit;
}

$miembro_id = intval($_GET['id']);

// Verificar que el miembro pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idmiembro = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$miembro_id, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: miembros.php");
    exit;
}

// Verificar si se ha proporcionado un estado
if (!isset($_GET['estado']) || ($_GET['estado'] != '0' && $_GET['estado'] != '1')) {
    header("Location: detalle_miembro.php?id=" . $miembro_id);
    exit;
}

$nuevo_estado = intval($_GET['estado']);

// Actualizar el estado de la membresía del miembro
try {
    $stmt = $pdo->prepare("UPDATE miembros SET estado_membresia = ? WHERE idmiembro = ? AND idgimnasio = ?");
    $stmt->execute([$nuevo_estado, $miembro_id, $gimnasio_id]);
    
    // Redirigir de vuelta a la página de detalle del miembro
    header("Location: detalle_miembro.php?id=" . $miembro_id . "&msg=membresia_actualizada");
    exit;
} catch (PDOException $e) {
    // En caso de error, redirigir con mensaje de error
    header("Location: detalle_miembro.php?id=" . $miembro_id . "&error=No se pudo actualizar el estado de la membresía");
    exit;
}
?>
