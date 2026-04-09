<?php
// controllers/logout_controller.php
session_start();

// Destruimos todas las variables de sesión
$_SESSION = array();

// Destruimos la sesión en el servidor
session_destroy();

// Redirigimos al inicio
header("Location: ../index.php");
exit();
?>