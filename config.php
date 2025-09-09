<?php
// Incluir archivo de configuración
session_start();
$host = 'localhost';
$dbname = 'gimnasio_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para limpiar datos de entrada
function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Configuración de errores para depuración
ini_set("display_errors", 0);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/app_error.log");

?>