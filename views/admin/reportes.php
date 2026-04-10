<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// 1. LÓGICA DE FILTROS (Por defecto el mes actual)
$desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-t');

// 2. Ingresos en el rango seleccionado
$query_ingresos = "SELECT COALESCE(SUM(Monto_Total), 0) as total FROM alquileres WHERE Estado IN ('Finalizado', 'En Curso') AND DATE(Fecha_Salida) BETWEEN ? AND ?";
$stmt_ingresos = $connection->prepare($query_ingresos);
$stmt_ingresos->bind_param("ss", $desde, $hasta);
$stmt_ingresos->execute();
$ingresos_totales = $stmt_ingresos->get_result()->fetch_assoc()['total'];

// 3. Contratos realizados en el rango seleccionado (Usando la función SQL)
$query_contratos = "
    SELECT a.ID_Alquiler, a.Fecha_Salida, a.Fecha_Devolucion_Real, a.Monto_Total, a.Estado,
           c.Nombre, c.Apellido, v.Marca, v.Placa,
           FN_Calcular_Extras_Contrato(a.ID_Alquiler) AS Extras
    FROM alquileres a
    JOIN clientes c ON a.ID_Cliente = c.ID_Cliente
    JOIN vehiculos v ON a.ID_Vehiculo = v.ID_Vehiculo
    WHERE DATE(a.Fecha_Salida) BETWEEN ? AND ?
    ORDER BY a.Fecha_Salida DESC
";
$stmt_contratos = $connection->prepare($query_contratos);
$stmt_contratos->bind_param("ss", $desde, $hasta);
$stmt_contratos->execute();
$contratos = $stmt_contratos->get_result();

// Cantidad de contratos encontrados
$total_contratos_rango = $contratos->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Gerenciales | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            body { background-color: white !important; }
            .no-print, aside, header { display: none !important; }
            .print-area { width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .print-border { border: 1px solid #e5e7eb !important; }
            @page { margin: 1cm; size: letter; }
        }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0 no-print">
        <div class="h-20 flex items-center justify-center border-b border-blue-800">
            <i class="fa-solid fa-car text-2xl mr-2"></i>
            <span class="font-bold text-lg tracking-widest">ADMIN PANEL</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-800' : 'text-blue-200 hover:bg-blue-800 hover:text-white'; ?> px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-chart-pie w-5"></i> Resumen
            </a>
            <a href="vehiculos.php" class="flex items-center gap-3 <?php echo basename($_SERVER['PHP_SELF']) == 'vehiculos.php' ? 'bg-blue-800' : 'text-blue-200 hover:bg-blue-800 hover:text-white'; ?> px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-car-side w-5"></i> Mi Flota
            </a>
            <a href="tarifas.php" class="flex items-center gap-3 <?php echo basename($_SERVER['PHP_SELF']) == 'tarifas.php' ? 'bg-blue-800' : 'text-blue-200 hover:bg-blue-800 hover:text-white'; ?> px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-tags w-5"></i> Tarifas
            </a>
            <a href="clientes.php" class="flex items-center gap-3 <?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'bg-blue-800' : 'text-blue-200 hover:bg-blue-800 hover:text-white'; ?> px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-users w-5"></i> Clientes
            </a>
            <a href="alquileres.php" class="flex items-center gap-3 <?php echo basename($_SERVER['PHP_SELF']) == 'alquileres.php' ? 'bg-blue-800' : 'text-blue-200 hover:bg-blue-800 hover:text-white'; ?> px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-file-invoice-dollar w-5"></i> Contratos
            </a>
            <a href="reportes.php" class="flex items-center gap-3 <?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800 hover:text-white'; ?> px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-file-lines w-5"></i> Reportes
            </a>
        </nav>
        <div class="p-4 border-t border-blue-800">
            <a href="../../index.php" class="flex items-center gap-3 text-blue-200 hover:text-white transition-colors text-sm">
                <i class="fa-solid fa-arrow-left"></i> Volver a la Web
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-y-auto print-area">
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 sticky top-0 z-10 no-print">
            <h1 class="text-2xl font-bold text-gray-800">Auditoría y Reportes</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600">Admin: <?php echo $_SESSION['username']; ?></span>
                <a href="../../controllers/logout_controller.php" class="text-red-500 hover:text-red-700" title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off text-lg"></i>
                </a>
            </div>
        </header>

        <div class="p-8 flex-1 bg-white print-area">
            
            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8 no-print">
                <form method="GET" action="reportes.php" class="flex flex-col md:flex-row md:items-end gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha Desde</label>
                        <input type="date" name="desde" value="<?php echo $desde; ?>" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fecha Hasta</label>
                        <input type="date" name="hasta" value="<?php echo $hasta; ?>" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none w-full">
                    </div>
                    <button type="submit" class="bg-blue-900 text-white px-6 py-2 rounded-lg font-bold shadow hover:bg-blue-800 transition-colors">
                        <i class="fa-solid fa-filter mr-2"></i> Filtrar Datos
                    </button>
                </form>
            </div>

            <div class="flex justify-between items-end border-b-2 border-gray-800 pb-6 mb-8 mt-4">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-widest text-gray-900">Reporte de Ventas</h2>
                    <p class="text-gray-500 mt-1 font-semibold">Período: <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></p>
                </div>
                <div class="text-right flex flex-col items-end">
                    <button onclick="window.print()" class="no-print bg-gray-800 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-black flex items-center gap-2 mb-4 transition-colors">
                        <i class="fa-solid fa-print"></i> Exportar a PDF
                    </button>
                    <p class="text-sm font-bold text-gray-500 uppercase">Fecha de Emisión</p>
                    <p class="text-xl font-mono text-gray-900"><?php echo date('d/m/Y h:i A'); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-10">
                <div class="bg-gray-50 p-6 rounded-lg print-border border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ingresos Generados en el Período</p>
                    <p class="text-4xl font-black text-blue-900">$<?php echo number_format($ingresos_totales, 2); ?></p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg print-border border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Contratos Registrados</p>
                    <p class="text-4xl font-black text-green-600"><?php echo $total_contratos_rango; ?> Operaciones</p>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Detalle de Operaciones</h3>
            
            <?php if($total_contratos_rango > 0): ?>
                <table class="w-full text-left text-sm print-border">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="p-3 font-bold uppercase">Nro.</th>
                            <th class="p-3 font-bold uppercase">Fecha Salida</th>
                            <th class="p-3 font-bold uppercase">Cliente</th>
                            <th class="p-3 font-bold uppercase">Vehículo</th>
                            <th class="p-3 font-bold uppercase">Estado</th>
                            <th class="p-3 font-bold uppercase text-right">Extras</th>
                            <th class="p-3 font-bold uppercase text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while($r = $contratos->fetch_assoc()): ?>
                        <tr>
                            <td class="p-3 font-mono font-bold">#<?php echo str_pad($r['ID_Alquiler'], 5, "0", STR_PAD_LEFT); ?></td>
                            <td class="p-3 text-gray-600"><?php echo date('d/m/Y', strtotime($r['Fecha_Salida'])); ?></td>
                            <td class="p-3 text-gray-800"><?php echo $r['Nombre'].' '.$r['Apellido']; ?></td>
                            <td class="p-3 text-gray-600"><?php echo $r['Marca'].' ('.$r['Placa'].')'; ?></td>
                            <td class="p-3 text-gray-600 font-semibold"><?php echo $r['Estado']; ?></td>
                            <td class="p-3 text-right text-gray-500">$<?php echo number_format($r['Extras'], 2); ?></td>
                            <td class="p-3 text-right font-bold text-gray-900">$<?php echo number_format($r['Monto_Total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8 bg-gray-50 rounded-lg">No hay contratos registrados en este rango de fechas.</p>
            <?php endif; ?>
            
            <div class="mt-12 text-center text-xs text-gray-400 border-t pt-4">
                Documento de auditoría generado automáticamente por el Sistema Administrativo Mundo Rental.
            </div>
        </div>
    </main>
</body>
</html>