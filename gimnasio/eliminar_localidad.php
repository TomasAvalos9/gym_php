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
    header("Location: otros.php");
    exit;
}

$idlocalidad = intval($_GET['id']);

// Verificar que el barrio pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Localidades WHERE idlocalidad = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$idlocalidad, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    $_SESSION['mensaje'] = "localidad no encontrada o no pertenece a tu gimnasio";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: otros.php");
    exit;
}

// Procesar la eliminación (borrado lógico)
try {
    $stmt = $pdo->prepare("UPDATE Localidades SET deleted = 1 WHERE idlocalidad = ? AND idgimnasio = ?");
    $stmt->execute([$idlocalidad, $gimnasio_id]);

    $_SESSION['mensaje'] = "Localidad eliminada correctamente";
    $_SESSION['tipo_mensaje'] = "success";
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al eliminar la Localidad: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: otros.php");
exit;
?>
