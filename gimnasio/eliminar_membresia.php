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
    header("Location: membresias.php");
    exit;
}

$membresia_id = intval($_GET['id']);

// Verificar que la membresia pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM membresias WHERE idmembresia = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$membresia_id, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: membresias.php");
    exit;
}

// Procesar la eliminación (borrado lógico)
try {
    $stmt = $pdo->prepare("UPDATE membresias SET deleted = 1 WHERE idmembresia = ? AND idgimnasio = ?");
    $stmt->execute([$membresia_id, $gimnasio_id]);
    
    // Redirigir a la lista de membresias con mensaje de éxito
    $_SESSION['mensaje'] = "Membresia eliminada correctamente";
    $_SESSION['tipo_mensaje'] = "success";
} catch (PDOException $e) {
    // Redirigir a la lista de membresias con mensaje de error
    $_SESSION['mensaje'] = "Error al eliminar la membresia: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: membresias.php");
exit;
?>
