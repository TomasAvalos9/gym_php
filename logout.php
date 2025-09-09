<?php
// Incluir archivo de configuración
require_once 'config.php';

// Destruir la sesión
session_start();
session_unset();
session_destroy();

// Redirigir al login
header("Location: index.php");
exit;
?>
