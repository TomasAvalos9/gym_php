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

// Verificar si se ha proporcionado un ID de miembro y un ID de rutina
if (!isset($_GET['miembro_id']) || empty($_GET['miembro_id']) || !isset($_GET['rutina_id']) || empty($_GET['rutina_id'])) {
    header("Location: miembros.php");
    exit;
}

$miembro_id = intval($_GET['miembro_id']);
$rutina_id = intval($_GET['rutina_id']);

// Verificar que el miembro pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM miembros WHERE idmiembro = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$miembro_id, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: miembros.php");
    exit;
}

// Verificar que la rutina pertenezca al gimnasio actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas WHERE idrutina = ? AND idgimnasio = ? AND deleted = 0");
$stmt->execute([$rutina_id, $gimnasio_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: detalle_miembro.php?id=" . $miembro_id);
    exit;
}

// Verificar que la asignación exista
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rutinas_miembro WHERE idmiembro = ? AND idrutina = ?");
$stmt->execute([$miembro_id, $rutina_id]);
if ($stmt->fetchColumn() == 0) {
    header("Location: detalle_miembro.php?id=" . $miembro_id);
    exit;
}

// Eliminar la asignación
try {
    $stmt = $pdo->prepare("DELETE FROM rutinas_miembro WHERE idmiembro = ? AND idrutina = ?");
    $stmt->execute([$miembro_id, $rutina_id]);
    
    // Redirigir de vuelta a la página de detalle del miembro
    header("Location: detalle_miembro.php?id=" . $miembro_id . "&msg=rutina_desasignada");
    exit;
} catch (PDOException $e) {
    // En caso de error, redirigir con mensaje de error
    header("Location: detalle_miembro.php?id=" . $miembro_id . "&error=No se pudo desasignar la rutina");
    exit;
}
?>
