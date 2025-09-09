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

// Procesar la eliminación (borrado lógico)
try {
    $stmt = $pdo->prepare("UPDATE miembros SET deleted = 1 WHERE idmiembro = ? AND idgimnasio = ?");
    $stmt->execute([$miembro_id, $gimnasio_id]);
    
    // Redirigir a la lista de miembros con mensaje de éxito
    $_SESSION['mensaje'] = "Miembro eliminado correctamente";
    $_SESSION['tipo_mensaje'] = "success";
} catch (PDOException $e) {
    // Redirigir a la lista de miembros con mensaje de error
    $_SESSION['mensaje'] = "Error al eliminar el miembro: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: miembros.php");
exit;
?>
