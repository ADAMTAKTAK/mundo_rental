<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    
    try {
        $id_cliente = $_POST['id_cliente'];
        $id_vehiculo = $_POST['id_vehiculo'];
        $fecha_salida = $_POST['fecha_salida'];
        $fecha_devolucion = $_POST['fecha_devolucion'];
        
        $servicios_seleccionados = isset($_POST['servicios']) ? $_POST['servicios'] : [];
        $accesorios_seleccionados = isset($_POST['accesorios']) ? $_POST['accesorios'] : [];
        
        $stmt_t = $connection->prepare("SELECT Monto_Diario FROM tarifas WHERE ID_Vehiculo = ? AND CURDATE() BETWEEN Fecha_Inicio AND Fecha_Fin LIMIT 1");
        $stmt_t->bind_param("i", $id_vehiculo);
        $stmt_t->execute();
        $precio_base = $stmt_t->get_result()->fetch_assoc()['Monto_Diario'];

        $dt_salida = new DateTime($fecha_salida);
        $dt_ahora = new DateTime();
        
        $estado_inicial = ($dt_salida > $dt_ahora) ? 'Reservado' : 'En Curso';

        $dt2 = new DateTime($fecha_devolucion);
        $horas = round(($dt_salida->diff($dt2)->days * 24) + $dt_salida->diff($dt2)->h);
        $dias = floor($horas / 24);
        $h_extra = $horas % 24;
        if($dias == 0) $dias = 1;
        if(($h_extra * 10) >= $precio_base) { $dias++; $h_extra = 0; }

        $total_fijo = 0;
        if(!empty($servicios_seleccionados)) {
            $ids = implode(',', array_map('intval', $servicios_seleccionados));
            $total_fijo = $connection->query("SELECT SUM(Precio_Base) as t FROM servicios WHERE ID_Servicio IN ($ids)")->fetch_assoc()['t'];
        }
        $total_acc = 0;
        if(!empty($accesorios_seleccionados)) {
            $ids = implode(',', array_map('intval', $accesorios_seleccionados));
            $total_acc = $connection->query("SELECT SUM(Precio_Diario) as t FROM accesorios WHERE ID_Accesorio IN ($ids)")->fetch_assoc()['t'] * $dias;
        }

        $monto_total = ($dias * $precio_base) + ($h_extra * 10) + $total_fijo + $total_acc + 100.00;

        $connection->begin_transaction();

        $stmt_ins = $connection->prepare("INSERT INTO alquileres (ID_Cliente, ID_Vehiculo, Fecha_Salida, Fecha_Devolucion_Prevista, Monto_Total, Deposito_Garantia, Estado_Deposito, Estado, Tiene_Servicios, Tiene_Accesorios) VALUES (?, ?, ?, ?, ?, 100.00, 'Retenido', ?, ?, ?)");
        $has_s = !empty($servicios_seleccionados) ? 1 : 0;
        $has_a = !empty($accesorios_seleccionados) ? 1 : 0;
        $stmt_ins->bind_param("iissdsii", $id_cliente, $id_vehiculo, $fecha_salida, $fecha_devolucion, $monto_total, $estado_inicial, $has_s, $has_a);
        $stmt_ins->execute();
        $id_alquiler = $connection->insert_id;

        foreach($servicios_seleccionados as $s) {
            $connection->query("INSERT INTO alquiler_servicios (ID_Alquiler, ID_Servicio, Precio_Cobrado) SELECT $id_alquiler, ID_Servicio, Precio_Base FROM servicios WHERE ID_Servicio = $s");
        }
        foreach($accesorios_seleccionados as $a) {
            $connection->query("INSERT INTO alquiler_accesorios (ID_Alquiler, ID_Accesorio, Cantidad, Precio_Cobrado) SELECT $id_alquiler, ID_Accesorio, 1, (Precio_Diario * $dias) FROM accesorios WHERE ID_Accesorio = $a");
        }

        $connection->commit();
        header("Location: ../views/admin/alquileres.php?success=alquiler_creado");

    } catch (Exception $e) {
        $connection->rollback();
        header("Location: ../views/admin/nuevo_alquiler.php?error=db");
    }
}