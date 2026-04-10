<?php
session_start();
require_once '../config/database_connection.php';

// 1. Seguridad: Solo los Administradores pueden mover los carros
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id_alquiler = (int)$_GET['id'];
    $accion = $_GET['accion'];

    try {
        if ($accion === 'entregar') {
            // Pasamos de Reservado a En Curso
            $estado = 'En Curso';
            $stmt = $connection->prepare("UPDATE alquileres SET Estado = ? WHERE ID_Alquiler = ?");
            $stmt->bind_param("si", $estado, $id_alquiler);
            $stmt->execute();
            $mensaje = "llaves_entregadas";
            
        } elseif ($accion === 'finalizar') {
            // Pasamos a Finalizado y estampamos la hora real de llegada
            $estado = 'Finalizado';
            $stmt = $connection->prepare("UPDATE alquileres SET Estado = ?, Fecha_Devolucion_Real = NOW() WHERE ID_Alquiler = ?");
            $stmt->bind_param("si", $estado, $id_alquiler);
            $stmt->execute();
            $mensaje = "vehiculo_recibido";
        }
        
        header("Location: ../views/admin/alquileres.php?success=" . $mensaje);
        
    } catch (Exception $e) {
        header("Location: ../views/admin/alquileres.php?error=db_error");
    }
} else {
    header("Location: ../views/admin/alquileres.php");
}
exit();
?>