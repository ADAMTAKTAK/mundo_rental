<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    // 1. Recibir datos del formulario
    $id_vehiculo = $_POST['id_vehiculo'];
    $fecha_salida = $_POST['fecha_salida'];
    $fecha_devolucion = $_POST['fecha_devolucion'];
    $id_usuario = $_SESSION['user_id'];

    // 2. Obtener el ID_Cliente real que está asociado a este ID_Usuario
    // (Porque tu tabla de alquileres pide el ID_Cliente, no el del usuario web)
    $stmt_user = $connection->prepare("SELECT ID_Cliente FROM usuarios WHERE ID_Usuario = ?");
    $stmt_user->bind_param("i", $id_usuario);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();
    
    // Si el usuario web no tiene un cliente asociado, hay un error de lógica
    if (empty($user_data['ID_Cliente'])) {
        header("Location: ../index.php?error=cliente_no_encontrado");
        exit();
    }
    
    $id_cliente = $user_data['ID_Cliente'];

    // 3. SEGURIDAD: Recalcular el precio en el servidor para evitar hackeos
    $stmt_tarifa = $connection->prepare("SELECT Monto_Diario FROM tarifas WHERE ID_Vehiculo = ? AND CURDATE() BETWEEN Fecha_Inicio AND Fecha_Fin LIMIT 1");
    $stmt_tarifa->bind_param("i", $id_vehiculo);
    $stmt_tarifa->execute();
    $tarifa_data = $stmt_tarifa->get_result()->fetch_assoc();
    $precio_base = $tarifa_data['Monto_Diario'];

    // Matemáticas en PHP (Días y Horas Extra a $10.00)
    $datetime_salida = new DateTime($fecha_salida);
    $datetime_devolucion = new DateTime($fecha_devolucion);
    $intervalo = $datetime_salida->diff($datetime_devolucion);
    
    // Convertir todo a horas redondeadas
    $horas_totales = round(($intervalo->days * 24) + $intervalo->h + ($intervalo->i / 60));
    
    $dias_cobrados = floor($horas_totales / 24);
    $horas_extra = $horas_totales % 24;
    
    // Regla de negocio: Mínimo 1 día de cobro
    if ($dias_cobrados == 0 && $horas_extra == 0) {
        $dias_cobrados = 1;
    } elseif ($dias_cobrados == 0 && $horas_extra > 0) {
        $dias_cobrados = 1;
        $horas_extra = 0;
    }
    
    // Calcular subtotal (Días + $10 por cada hora extra)
    $subtotal = ($dias_cobrados * $precio_base) + ($horas_extra * 10.00);
    $monto_total = $subtotal + 100.00; // Alquiler + Depósito fijo

    // 4. LLAMAR A TU PROCEDIMIENTO ALMACENADO (SP_Nueva_Reserva)
    // Pasamos 0 y 0 al final porque aún no tenemos checkboxes para servicios/accesorios
    try {
        $stmt_reserva = $connection->prepare("CALL SP_Nueva_Reserva(?, ?, ?, ?, ?, 0, 0)");
        $stmt_reserva->bind_param("iissd", $id_cliente, $id_vehiculo, $fecha_salida, $fecha_devolucion, $monto_total);
        
        if ($stmt_reserva->execute()) {
            // ¡Éxito! Reserva guardada. Lo mandamos al inicio con un mensaje.
            header("Location: ../index.php?success=reserva_completada");
            exit();
        } else {
            header("Location: ../views/reservas/checkout.php?id=$id_vehiculo&error=fallo_db");
            exit();
        }
    } catch (Exception $e) {
        header("Location: ../views/reservas/checkout.php?id=$id_vehiculo&error=excepcion");
        exit();
    }
} else {
    // Si intentan entrar por URL directamente
    header("Location: ../index.php");
    exit();
}
?>