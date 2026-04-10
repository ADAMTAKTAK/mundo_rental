<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: mis_reservas.php");
    exit();
}

$id_alquiler = (int)$_GET['id'];
$id_usuario = $_SESSION['user_id'];

$query = "
    SELECT a.*, 
           c.Nombre AS CliNom, c.Apellido AS CliApe, c.Tipo_Documento, c.Numero_Documento, c.Telefono, c.Email,
           v.Marca, v.Modelo, v.Placa, v.Anio, v.Color
    FROM alquileres a
    JOIN clientes c ON a.ID_Cliente = c.ID_Cliente
    JOIN usuarios u ON c.ID_Cliente = u.ID_Cliente
    JOIN vehiculos v ON a.ID_Vehiculo = v.ID_Vehiculo
    WHERE a.ID_Alquiler = ? AND u.ID_Usuario = ?
";
$stmt = $connection->prepare($query);
$stmt->bind_param("ii", $id_alquiler, $id_usuario);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

if (!$reserva) {
    header("Location: mis_reservas.php");
    exit();
}

$servicios = $connection->query("SELECT s.Nombre, asrv.Precio_Cobrado FROM alquiler_servicios asrv JOIN servicios s ON asrv.ID_Servicio = s.ID_Servicio WHERE asrv.ID_Alquiler = $id_alquiler");
$accesorios = $connection->query("SELECT acc.Nombre, aacc.Cantidad, aacc.Precio_Cobrado FROM alquiler_accesorios aacc JOIN accesorios acc ON aacc.ID_Accesorio = acc.ID_Accesorio WHERE aacc.ID_Alquiler = $id_alquiler");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo $id_alquiler; ?> | Mundo Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reglas estrictas para el generador de PDF del navegador */
        @media print {
            body { background-color: white !important; }
            .no-print { display: none !important; }
            .print-border { border: 1px solid #e5e7eb !important; }
            @page { margin: 1.5cm; size: letter; }
        }
    </style>
</head>
<body class="bg-gray-100 py-10 px-4 text-gray-800">

    <div class="max-w-3xl mx-auto">
        
        <div class="mb-6 flex justify-between items-center no-print">
            <a href="mis_reservas.php" class="text-blue-900 font-bold hover:underline">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
            <button onclick="window.print()" class="px-6 py-3 bg-blue-900 text-white font-bold rounded-lg shadow-lg hover:bg-blue-800 flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> Guardar como PDF
            </button>
        </div>

        <div class="bg-white p-12 rounded-lg shadow-xl print-border border border-gray-200">
            
            <div class="flex justify-between items-start border-b-2 border-gray-800 pb-8 mb-8">
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-widest text-gray-900">Mundo Rental</h1>
                    <p class="text-gray-500 text-sm mt-1">Isla de Margarita, Nueva Esparta</p>
                    <p class="text-gray-500 text-sm">RIF: J-12345678-9</p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-bold uppercase text-gray-400 tracking-widest mb-2">Recibo de Reserva</p>
                    <p class="text-2xl font-mono font-black text-gray-900">#<?php echo str_pad($id_alquiler, 6, "0", STR_PAD_LEFT); ?></p>
                    <p class="text-sm text-gray-500 mt-2">Fecha: <?php echo date('d/m/Y'); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-12 mb-10">
                <div>
                    <h3 class="text-xs font-black uppercase text-gray-400 mb-2">Datos del Cliente</h3>
                    <p class="font-bold text-lg"><?php echo $reserva['CliNom'].' '.$reserva['CliApe']; ?></p>
                    <p class="text-sm text-gray-600"><?php echo $reserva['Tipo_Documento'].'-'.$reserva['Numero_Documento']; ?></p>
                    <p class="text-sm text-gray-600"><?php echo $reserva['Telefono']; ?></p>
                    <p class="text-sm text-gray-600"><?php echo $reserva['Email']; ?></p>
                </div>
                <div>
                    <h3 class="text-xs font-black uppercase text-gray-400 mb-2">Vehículo Reservado</h3>
                    <p class="font-bold text-lg"><?php echo $reserva['Marca'].' '.$reserva['Modelo']; ?></p>
                    <p class="text-sm text-gray-600 font-mono">Placa: <?php echo $reserva['Placa']; ?></p>
                    <p class="text-sm text-gray-600"><?php echo $reserva['Anio'].' - '.$reserva['Color']; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-6 rounded-lg mb-10 border border-gray-200">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Fecha de Retiro</p>
                    <p class="font-bold text-gray-800"><?php echo date('d/m/Y h:i A', strtotime($reserva['Fecha_Salida'])); ?></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Fecha de Devolución</p>
                    <p class="font-bold text-gray-800"><?php echo date('d/m/Y h:i A', strtotime($reserva['Fecha_Devolucion_Prevista'])); ?></p>
                </div>
            </div>

            <h3 class="text-xs font-black uppercase text-gray-400 mb-4 border-b border-gray-200 pb-2">Desglose Financiero</h3>
            <table class="w-full text-left mb-8 text-sm">
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-3 font-bold uppercase">Concepto</th>
                        <th class="py-3 font-bold uppercase text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-4 text-gray-800">Alquiler de Vehículo (<?php echo $reserva['Dias_Cobrados'] ?? 'Calculado por fechas'; ?>)</td>
                        <td class="py-4 text-right font-medium text-gray-800">$<?php echo number_format($reserva['Monto_Total'] - $reserva['Deposito_Garantia'] - 0 /* Aquí irían los extras si los restamos */, 2); ?></td>
                    </tr>
                    
                    <?php while($s = $servicios->fetch_assoc()): ?>
                    <tr>
                        <td class="py-4 text-gray-600">Servicio Adicional: <?php echo $s['Nombre']; ?></td>
                        <td class="py-4 text-right text-gray-600">+$<?php echo number_format($s['Precio_Cobrado'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>

                    <?php while($a = $accesorios->fetch_assoc()): ?>
                    <tr>
                        <td class="py-4 text-gray-600">Accesorio: <?php echo $a['Nombre']; ?> (x<?php echo $a['Cantidad']; ?>)</td>
                        <td class="py-4 text-right text-gray-600">+$<?php echo number_format($a['Precio_Cobrado'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>

                    <tr>
                        <td class="py-4 text-gray-500 italic">Depósito de Garantía Reembolsable</td>
                        <td class="py-4 text-right text-gray-500 italic">+$<?php echo number_format($reserva['Deposito_Garantia'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="border-t-2 border-gray-800 pt-6 flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Estado del Pago</p>
                    <p class="font-black text-green-600 uppercase tracking-widest">Confirmado</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-1">Total Pagado</p>
                    <p class="text-3xl font-black text-gray-900">$<?php echo number_format($reserva['Monto_Total'], 2); ?></p>
                </div>
            </div>
            
            <div class="mt-12 text-center text-xs text-gray-400 border-t border-gray-100 pt-6">
                <p>Este documento es un comprobante válido de su reserva electrónica.</p>
                <p>Por favor, presente este recibo digital o impreso al momento de retirar su vehículo.</p>
            </div>
        </div>
    </div>
</body>
</html>