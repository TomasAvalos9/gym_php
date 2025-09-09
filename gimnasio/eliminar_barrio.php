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

// Verificar si se ha proporcionado un ID de barrio
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: barrios.php");
    exit;
}

$idbarrio = intval($_GET['id']);

// Verificar que el barrio pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Barrios WHERE idbarrio = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$idbarrio, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    $_SESSION['mensaje'] = "Barrio no encontrado o no pertenece a tu gimnasio";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: otros.php");
    exit;
}

// Procesar la eliminación (borrado lógico)
try {
    $stmt = $pdo->prepare("UPDATE Barrios SET deleted = 1 WHERE idbarrio = ? AND idgimnasio = ?");
    $stmt->execute([$idbarrio, $gimnasio_id]);

    $_SESSION['mensaje'] = "Barrio eliminado correctamente";
    $_SESSION['tipo_mensaje'] = "success";
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al eliminar el barrio: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: otros.php");
exit;
?>
