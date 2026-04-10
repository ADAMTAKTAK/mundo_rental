<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "reservacarros";

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die("Error de conexión: " . $connection->connect_error);
}

$connection->set_charset("utf8");
?>