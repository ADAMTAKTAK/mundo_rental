<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    
    $id_tarifa = (int)$_POST['id_tarifa'];
    $id_vehiculo = (int)$_POST['id_vehiculo'];
    $monto = $_POST['monto'];
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];

    $stmt_check = $connection->prepare("SELECT Fecha_Inicio FROM tarifas WHERE ID_Tarifa = ?");
    $stmt_check->bind_param("i", $id_tarifa);
    $stmt_check->execute();
    $tarifa_bd = $stmt_check->get_result()->fetch_assoc();

    $hoy = date('Y-m-d');
    
    if ($tarifa_bd['Fecha_Inicio'] <= $hoy) {
        header("Location: ../views/admin/tarifas.php?error=editar_activa");
        exit();
    }

    if ($inicio > $fin) {
        header("Location: ../views/admin/editar_tarifa.php?id=$id_tarifa&error=fechas_incoherentes");
        exit();
    }

    $query_choque = "SELECT ID_Tarifa FROM tarifas WHERE ID_Vehiculo = ? AND ID_Tarifa != ? AND (? <= Fecha_Fin AND ? >= Fecha_Inicio)";
    $stmt_choque = $connection->prepare($query_choque);
    $stmt_choque->bind_param("iiss", $id_vehiculo, $id_tarifa, $inicio, $fin);
    $stmt_choque->execute();
    
    if ($stmt_choque->get_result()->num_rows > 0) {
        header("Location: ../views/admin/editar_tarifa.php?id=$id_tarifa&error=choque_fechas");
        exit();
    }

    $stmt_update = $connection->prepare("UPDATE tarifas SET ID_Vehiculo = ?, Monto_Diario = ?, Fecha_Inicio = ?, Fecha_Fin = ? WHERE ID_Tarifa = ?");
    $stmt_update->bind_param("idssi", $id_vehiculo, $monto, $inicio, $fin, $id_tarifa);

    if ($stmt_update->execute()) {
        header("Location: ../views/admin/tarifas.php?success=actualizado");
    } else {
        header("Location: ../views/admin/editar_tarifa.php?id=$id_tarifa&error=db");
    }
    exit();
} else {
    header("Location: ../views/admin/tarifas.php");
    exit();
}
?>