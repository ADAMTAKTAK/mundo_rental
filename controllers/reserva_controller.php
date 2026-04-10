<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    try {
        $id_vehiculo = $_POST['id_vehiculo'];
        $fecha_salida = $_POST['fecha_salida'];
        $fecha_devolucion = $_POST['fecha_devolucion'];
        $id_usuario = $_SESSION['user_id'];
        
        // Arrays de Adicionales
        $servicios_seleccionados = isset($_POST['servicios']) ? $_POST['servicios'] : [];
        $accesorios_seleccionados = isset($_POST['accesorios']) ? $_POST['accesorios'] : [];
        $tiene_servicios = !empty($servicios_seleccionados) ? 1 : 0;
        $tiene_accesorios = !empty($accesorios_seleccionados) ? 1 : 0;

        // 1. Obtener ID del Cliente (Con seguridad de rebote al index)
        $stmt_user = $connection->prepare("SELECT ID_Cliente FROM usuarios WHERE ID_Usuario = ?");
        $stmt_user->bind_param("i", $id_usuario);
        $stmt_user->execute();
        $user_data = $stmt_user->get_result()->fetch_assoc();
        
        if (empty($user_data['ID_Cliente'])) {
            header("Location: ../index.php?error=perfil_incompleto");
            exit();
        }
        $id_cliente = $user_data['ID_Cliente'];

        // 2. Obtener Precio Base Diario
        $stmt_tarifa = $connection->prepare("SELECT Monto_Diario FROM tarifas WHERE ID_Vehiculo = ? AND CURDATE() BETWEEN Fecha_Inicio AND Fecha_Fin LIMIT 1");
        $stmt_tarifa->bind_param("i", $id_vehiculo);
        $stmt_tarifa->execute();
        $tarifa_data = $stmt_tarifa->get_result()->fetch_assoc();
        
        if (!$tarifa_data) throw new Exception("Sin tarifa activa.");
        $precio_base = $tarifa_data['Monto_Diario'];

        // 3. Formatear fechas para la Base de Datos
        $dt_salida = new DateTime($fecha_salida);
        $dt_devolucion = new DateTime($fecha_devolucion);
        $fecha_salida_db = $dt_salida->format('Y-m-d H:i:s');
        $fecha_devolucion_db = $dt_devolucion->format('Y-m-d H:i:s');

        // 4. Matemáticas de Tiempo y Tope de Horas Extra
        $intervalo = $dt_salida->diff($dt_devolucion);
        $horas_totales = round(($intervalo->days * 24) + $intervalo->h + ($intervalo->i / 60));
        
        $dias_cobrados = floor($horas_totales / 24);
        $horas_extra = $horas_totales % 24;
        
        if ($dias_cobrados == 0 && $horas_extra == 0) $dias_cobrados = 1;
        elseif ($dias_cobrados == 0 && $horas_extra > 0) { $dias_cobrados = 1; $horas_extra = 0; }

        $costo_horas_extra = $horas_extra * 10.00;
        if ($costo_horas_extra >= $precio_base) {
            $dias_cobrados += 1;
            $costo_horas_extra = 0;
        }

        // 5. Cálculos de Extras
        $total_servicios_fijos = 0;
        if($tiene_servicios) {
            $ids = implode(',', array_map('intval', $servicios_seleccionados));
            $res = $connection->query("SELECT SUM(Precio_Base) as total FROM servicios WHERE ID_Servicio IN ($ids)");
            $total_servicios_fijos = $res->fetch_assoc()['total'];
        }

        $total_accesorios_dia = 0;
        if($tiene_accesorios) {
            $ids = implode(',', array_map('intval', $accesorios_seleccionados));
            $res = $connection->query("SELECT SUM(Precio_Diario) as total FROM accesorios WHERE ID_Accesorio IN ($ids)");
            $total_accesorios_dia = $res->fetch_assoc()['total'];
        }

        $monto_total = ($dias_cobrados * $precio_base) + $costo_horas_extra + ($dias_cobrados * $total_accesorios_dia) + $total_servicios_fijos + 100.00; 

        // 6. INSERCIÓN TRANSACCIONAL (TODO O NADA)
        $connection->begin_transaction();

        $stmt_reserva = $connection->prepare("CALL SP_Nueva_Reserva(?, ?, ?, ?, ?, ?, ?)");
        $stmt_reserva->bind_param("iissdii", $id_cliente, $id_vehiculo, $fecha_salida_db, $fecha_devolucion_db, $monto_total, $tiene_servicios, $tiene_accesorios);
        $stmt_reserva->execute();
        
        // Recuperar el ID del alquiler recién creado
        $id_alquiler = $connection->insert_id;
        if ($id_alquiler == 0) {
            $stmt_id = $connection->prepare("SELECT MAX(ID_Alquiler) as id FROM alquileres WHERE ID_Cliente = ?");
            $stmt_id->bind_param("i", $id_cliente);
            $stmt_id->execute();
            $id_alquiler = $stmt_id->get_result()->fetch_assoc()['id'];
        }

        // Guardar detalle de Servicios Fijos
        if($tiene_servicios) {
            $stmt_srv = $connection->prepare("INSERT INTO alquiler_servicios (ID_Alquiler, ID_Servicio, Precio_Cobrado) SELECT ?, ID_Servicio, Precio_Base FROM servicios WHERE ID_Servicio = ?");
            foreach($servicios_seleccionados as $id_s) {
                $stmt_srv->bind_param("ii", $id_alquiler, $id_s);
                $stmt_srv->execute();
            }
        }

        // Guardar detalle de Accesorios por Día
        if($tiene_accesorios) {
            $stmt_acc = $connection->prepare("INSERT INTO alquiler_accesorios (ID_Alquiler, ID_Accesorio, Cantidad, Precio_Cobrado) SELECT ?, ID_Accesorio, 1, (Precio_Diario * ?) FROM accesorios WHERE ID_Accesorio = ?");
            foreach($accesorios_seleccionados as $id_a) {
                $stmt_acc->bind_param("idi", $id_alquiler, $dias_cobrados, $id_a);
                $stmt_acc->execute();
            }
        }

        $connection->commit();
        
        // ¡Victoria! Redirigimos al inicio.
        header("Location: ../index.php?success=reserva_completada");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        // Si hay error, regresamos al checkout silenciosamente pero con la alerta activada
        header("Location: ../views/reservas/checkout.php?id=$id_vehiculo&error=db_error");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>