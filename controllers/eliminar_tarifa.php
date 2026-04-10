<?php
session_start();
require_once '../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_tarifa = (int)$_GET['id'];

    $stmt_check = $connection->prepare("SELECT Fecha_Fin FROM tarifas WHERE ID_Tarifa = ?");
    $stmt_check->bind_param("i", $id_tarifa);
    $stmt_check->execute();
    $tarifa = $stmt_check->get_result()->fetch_assoc();

    $hoy = date('Y-m-d');
    if ($tarifa['Fecha_Fin'] >= $hoy) {
        header("Location: ../views/admin/tarifas.php?error=tarifa_activa");
        exit();
    }

    $stmt = $connection->prepare("DELETE FROM tarifas WHERE ID_Tarifa = ?");
    $stmt->bind_param("i", $id_tarifa);
    
    if ($stmt->execute()) {
        header("Location: ../views/admin/tarifas.php?success=eliminado");
    } else {
        header("Location: ../views/admin/tarifas.php?error=db");
    }
} else {
    header("Location: ../views/admin/tarifas.php");
}
exit();
?>