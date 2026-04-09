<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    $id_vehiculo = $_POST['id_vehiculo'];
    $monto = $_POST['monto'];
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];

    $stmt = $connection->prepare("INSERT INTO tarifas (ID_Vehiculo, Monto_Diario, Fecha_Inicio, Fecha_Fin) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $id_vehiculo, $monto, $inicio, $fin);

    if ($stmt->execute()) {
        header("Location: ../views/admin/tarifas.php?success=1");
    } else {
        header("Location: ../views/admin/nueva_tarifa.php?error=1");
    }
    exit();
}