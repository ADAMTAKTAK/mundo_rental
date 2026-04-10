<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: alquileres.php");
    exit();
}

$id_alquiler = (int)$_GET['id'];

$query = "
    SELECT a.*, 
           c.Nombre AS CliNom, c.Apellido AS CliApe, c.Tipo_Documento, c.Numero_Documento, c.Telefono, c.Email, c.Licencia_Conducir,
           v.Marca, v.Modelo, v.Placa, v.Anio, v.Color, v.Imagen_URL
    FROM alquileres a
    JOIN clientes c ON a.ID_Cliente = c.ID_Cliente
    JOIN vehiculos v ON a.ID_Vehiculo = v.ID_Vehiculo
    WHERE a.ID_Alquiler = ?
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $id_alquiler);
$stmt->execute();
$alquiler = $stmt->get_result()->fetch_assoc();

if (!$alquiler) {
    header("Location: alquileres.php?error=no_encontrado");
    exit();
}

$servicios = $connection->query("
    SELECT s.Nombre, asrv.Precio_Cobrado 
    FROM alquiler_servicios asrv
    JOIN servicios s ON asrv.ID_Servicio = s.ID_Servicio
    WHERE asrv.ID_Alquiler = $id_alquiler
");

$accesorios = $connection->query("
    SELECT acc.Nombre, aacc.Cantidad, aacc.Precio_Cobrado 
    FROM alquiler_accesorios aacc
    JOIN accesorios acc ON aacc.ID_Accesorio = acc.ID_Accesorio
    WHERE aacc.ID_Alquiler = $id_alquiler
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Contrato #<?php echo $id_alquiler; ?> | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 py-10 px-4">

    <div class="max-w-4xl mx-auto">
        
        <div class="mb-6">
            <a href="alquileres.php" class="text-blue-900 hover:text-blue-700 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Volver a la Lista
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            
            <div class="bg-blue-900 p-8 text-white flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-black tracking-tighter uppercase">Contrato de Alquiler</h1>
                    <p class="text-blue-200 mt-1 italic">Mundo Rental Margarita C.A.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm uppercase font-bold text-blue-300">Nro. Control</p>
                    <p class="text-3xl font-mono">#<?php echo str_pad($id_alquiler, 6, "0", STR_PAD_LEFT); ?></p>
                    <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-bold bg-white/20 uppercase">
                        <?php echo $alquiler['Estado']; ?>
                    </span>
                </div>
            </div>

            <div class="p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                    
                    <div>
                        <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-4 border-b pb-2">Datos del Arrendatario</h3>
                        <p class="text-xl font-bold text-gray-800"><?php echo $alquiler['CliNom'].' '.$alquiler['CliApe']; ?></p>
                        <p class="text-gray-500 font-medium"><?php echo $alquiler['Tipo_Documento'].'-'.$alquiler['Numero_Documento']; ?></p>
                        <div class="mt-4 space-y-1 text-sm text-gray-600">
                            <p><i class="fa-solid fa-phone w-5"></i> <?php echo $alquiler['Telefono']; ?></p>
                            <p><i class="fa-solid fa-envelope w-5"></i> <?php echo $alquiler['Email']; ?></p>
                            <p><i class="fa-solid fa-address-card w-5"></i> Licencia: <span class="font-mono"><?php echo $alquiler['Licencia_Conducir']; ?></span></p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-4 border-b pb-2">Vehículo Asignado</h3>
                        <div class="flex gap-4">
                            <div class="w-24 h-16 rounded-lg bg-gray-100 overflow-hidden border">
                                <img src="../../<?php echo $alquiler['Imagen_URL']; ?>" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-xl font-bold text-gray-800"><?php echo $alquiler['Marca'].' '.$alquiler['Modelo']; ?></p>
                                <p class="text-blue-600 font-black font-mono tracking-tighter"><?php echo $alquiler['Placa']; ?></p>
                                <p class="text-xs text-gray-400 mt-1 uppercase"><?php echo $alquiler['Anio'].' • '.$alquiler['Color']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="text-center md:text-left border-b md:border-b-0 md:border-r border-gray-200 pb-4 md:pb-0">
                        <p class="text-xs text-gray-400 uppercase font-bold mb-1">Salida de Oficina</p>
                        <p class="font-bold text-gray-800"><?php echo date('d/m/Y - h:i a', strtotime($alquiler['Fecha_Salida'])); ?></p>
                    </div>
                    <div class="text-center md:text-left border-b md:border-b-0 md:border-r border-gray-200 pb-4 md:pb-0">
                        <p class="text-xs text-gray-400 uppercase font-bold mb-1">Retorno Previsto</p>
                        <p class="font-bold text-gray-800"><?php echo date('d/m/Y - h:i a', strtotime($alquiler['Fecha_Devolucion_Prevista'])); ?></p>
                    </div>
                    <div class="text-center md:text-left">
                        <p class="text-xs text-gray-400 uppercase font-bold mb-1">Días Calculados</p>
                        <p class="font-bold text-blue-900 text-xl"><?php echo $alquiler['Dias_Cobrados'] ?? '---'; ?> día(s)</p>
                        <?php if($alquiler['Horas_Extra'] > 0): ?>
                            <span class="text-xs text-orange-600">+ <?php echo $alquiler['Horas_Extra']; ?> horas extra</span>
                        <?php endif; ?>
                    </div>
                </div>

                <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-4">Desglose Financiero</h3>
                <div class="overflow-hidden border border-gray-100 rounded-xl mb-8">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-400 uppercase text-[10px] tracking-widest font-black">
                            <tr>
                                <th class="px-6 py-3">Concepto</th>
                                <th class="px-6 py-3 text-right">Monto Unitario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <tr>
                                <td class="px-6 py-4 font-medium">Alquiler de Vehículo (<?php echo $alquiler['Dias_Cobrados']; ?> días)</td>
                                <td class="px-6 py-4 text-right">$<?php echo number_format($alquiler['Monto_Total'] - $alquiler['Monto_Horas_Extra'] - 100 - 0 /*ajuste manual*/, 2); ?></td>
                            </tr>
                            
                            <?php if($alquiler['Horas_Extra'] > 0): ?>
                            <tr>
                                <td class="px-6 py-4 font-medium"><?php echo $alquiler['Horas_Extra']; ?> Horas Adicionales ($10.00 c/u)</td>
                                <td class="px-6 py-4 text-right text-orange-600">+$<?php echo number_format($alquiler['Monto_Horas_Extra'], 2); ?></td>
                            </tr>
                            <?php endif; ?>

                            <?php while($s = $servicios->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 font-medium">Servicio: <?php echo $s['Nombre']; ?></td>
                                <td class="px-6 py-4 text-right">+$<?php echo number_format($s['Precio_Cobrado'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>

                            <?php while($a = $accesorios->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 font-medium">Accesorio: <?php echo $a['Nombre']; ?> (x<?php echo $a['Cantidad']; ?>)</td>
                                <td class="px-6 py-4 text-right">+$<?php echo number_format($a['Precio_Cobrado'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <tr class="bg-gray-50/50 italic">
                                <td class="px-6 py-4 text-gray-500 font-medium">Depósito en Garantía (<?php echo $alquiler['Estado_Deposito']; ?>)</td>
                                <td class="px-6 py-4 text-right text-gray-500">+$<?php echo number_format($alquiler['Deposito_Garantia'], 2); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-blue-50">
                            <tr>
                                <td class="px-6 py-5 text-blue-900 font-black text-lg">TOTAL NETO DEL CONTRATO</td>
                                <td class="px-6 py-5 text-right text-blue-900 font-black text-2xl">$<?php echo number_format($alquiler['Monto_Total'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="border-t border-dashed border-gray-200 pt-8 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-4">Mundo Rental • Margarita Island • Reservacarros v5.0</p>
                    <div class="flex justify-center gap-4">
                        <button onclick="window.print()" class="px-6 py-2 bg-gray-800 text-white rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-black transition-colors">
                            <i class="fa-solid fa-print"></i> Imprimir Contrato
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>