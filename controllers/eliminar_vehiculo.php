<?php
session_start();
require_once '../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_vehiculo = (int)$_GET['id'];

    try {
        $stmt = $connection->prepare("DELETE FROM vehiculos WHERE ID_Vehiculo = ?");
        $stmt->bind_param("i", $id_vehiculo);
        
        if ($stmt->execute()) {
            header("Location: ../views/admin/vehiculos.php?success=eliminado");
        } else {
            header("Location: ../views/admin/vehiculos.php?error=en_uso");
        }
    } catch (mysqli_sql_exception $e) {
        header("Location: ../views/admin/vehiculos.php?error=en_uso");
    }
} else {
    header("Location: ../views/admin/vehiculos.php");
}
exit();
?>