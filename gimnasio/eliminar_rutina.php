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

// Verificar si se ha proporcionado un ID de rutina
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: rutinas.php");
    exit;
}

$rutina_id = intval($_GET['id']);

// Verificar que la rutina pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas WHERE idrutina = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$rutina_id, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: rutinas.php");
    exit;
}

// Procesar la eliminación (borrado lógico)
try {
    $stmt = $pdo->prepare("UPDATE rutinas SET deleted = 1 WHERE idrutina = ? AND idgimnasio = ?");
    $stmt->execute([$rutina_id, $gimnasio_id]);
    
    // Redirigir a la lista de rutinas con mensaje de éxito
    $_SESSION['mensaje'] = "Rutina eliminada correctamente";
    $_SESSION['tipo_mensaje'] = "success";
} catch (PDOException $e) {
    // Redirigir a la lista de rutinas con mensaje de error
    $_SESSION['mensaje'] = "Error al eliminar la rutina: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: rutinas.php");
exit;
?>
