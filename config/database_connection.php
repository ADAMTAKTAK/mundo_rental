<?php
$host = "localhost";
$user = "root"; // Usuario por defecto en Laragon/XAMPP
$password = ""; // Déjalo vacío si no le tienes clave a tu MySQL
$database = "reservacarros"; // Asegúrate de que este sea el nombre exacto de tu BD

// Crear conexión
$connection = new mysqli($host, $user, $password, $database);

// Comprobar la conexión
if ($connection->connect_error) {
    die("Error de conexión: " . $connection->connect_error);
}

// Establecer charset para evitar problemas con acentos y caracteres especiales (ñ, tildes)
$connection->set_charset("utf8");
?>