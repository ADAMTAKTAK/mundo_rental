<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    $id_vehiculo = $_POST['id_vehiculo'];
    $monto = $_POST['monto'];
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];

    // 1. Validación básica: Inicio no puede ser mayor que el fin
    if ($inicio > $fin) {
        header("Location: ../views/admin/nueva_tarifa.php?error=fechas_incoherentes");
        exit();
    }

    // 2. Validación Anti-Choque: Buscar si este carro ya tiene una tarifa en estas fechas
    $query_choque = "SELECT ID_Tarifa FROM tarifas WHERE ID_Vehiculo = ? AND (? <= Fecha_Fin AND ? >= Fecha_Inicio)";
    $stmt_check = $connection->prepare($query_choque);
    $stmt_check->bind_param("iss", $id_vehiculo, $inicio, $fin);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows > 0) {
        // Hay un choque de fechas
        header("Location: ../views/admin/nueva_tarifa.php?error=choque_fechas");
        exit();
    }

    // 3. Inserción normal si pasa las pruebas
    $stmt = $connection->prepare("INSERT INTO tarifas (ID_Vehiculo, Monto_Diario, Fecha_Inicio, Fecha_Fin) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $id_vehiculo, $monto, $inicio, $fin);

    if ($stmt->execute()) {
        header("Location: ../views/admin/tarifas.php?success=1");
    } else {
        header("Location: ../views/admin/nueva_tarifa.php?error=db");
    }
    exit();
}
?>