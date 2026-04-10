<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$res_total_tarifas = $connection->query("SELECT COUNT(*) as total FROM tarifas");
$total_tarifas = $res_total_tarifas->fetch_assoc()['total'];

$registros_por_pagina = 10;
$total_paginas = ceil($total_tarifas / $registros_por_pagina);

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
if ($pagina_actual > $total_paginas && $total_paginas > 0) $pagina_actual = $total_paginas;

$offset = max(0, ($pagina_actual - 1) * $registros_por_pagina);

$query_tarifas = "
    SELECT t.*, v.Marca, v.Modelo, v.Placa 
    FROM tarifas t 
    JOIN vehiculos v ON t.ID_Vehiculo = v.ID_Vehiculo 
    ORDER BY t.Fecha_Inicio DESC 
    LIMIT $offset, $registros_por_pagina
";
$resultado_tarifas = $connection->query($query_tarifas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Tarifas | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden"> 
    <aside class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0">
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

    <main class="flex-1 flex flex-col overflow-y-auto"> <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 sticky top-0 z-10">
            <h1 class="text-2xl font-bold text-gray-800">Configuración de Precios</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600">Admin: <?php echo $_SESSION['username']; ?></span>
                <a href="../../controllers/logout_controller.php" class="text-red-500 hover:text-red-700" title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off text-lg"></i>
                </a>
            </div>
        </header>

        <div class="p-8 flex-1">
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-100 text-green-700 p-4 mb-6 rounded-lg text-sm font-semibold">
                    <?php 
                        if($_GET['success'] == '1') echo '<i class="fa-solid fa-check mr-2"></i> Tarifa creada exitosamente.';
                        if($_GET['success'] == 'actualizado') echo '<i class="fa-solid fa-check mr-2"></i> Tarifa actualizada con éxito.';
                        if($_GET['success'] == 'eliminado') echo '<i class="fa-solid fa-trash mr-2"></i> Tarifa eliminada del sistema.';
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm font-semibold border-l-4 border-red-500">
                    <?php 
                        if($_GET['error'] == 'tarifa_activa') echo '<i class="fa-solid fa-triangle-exclamation mr-2"></i> No puedes eliminar una tarifa que está actualmente en uso o programada para el futuro.';
                        if($_GET['error'] == 'editar_activa') echo '<i class="fa-solid fa-shield-halved mr-2"></i> Por seguridad, no puedes editar una tarifa que ya entró en vigencia o que pertenece al pasado. Solo puedes editar tarifas futuras.';
                    ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-100 text-green-700 p-4 mb-6 rounded-lg text-sm font-semibold">
                    <?php 
                        if($_GET['success'] == '1') echo '<i class="fa-solid fa-check mr-2"></i> Tarifa creada exitosamente.';
                        if($_GET['success'] == 'actualizado') echo '<i class="fa-solid fa-check mr-2"></i> Tarifa actualizada con éxito.';
                        if($_GET['success'] == 'eliminado') echo '<i class="fa-solid fa-trash mr-2"></i> Tarifa eliminada del sistema.';
                    ?>
                </div>
            <?php endif; ?>
            <div class="flex justify-between items-center mb-8">
                <p class="text-gray-500">Administra los precios por temporada de tus vehículos.</p>
                <a href="nueva_tarifa.php" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md">
                    <i class="fa-solid fa-plus mr-2"></i> Asignar Nueva Tarifa
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                            <th class="px-6 py-4">Vehículo</th>
                            <th class="px-6 py-4">Monto Diario</th>
                            <th class="px-6 py-4">Vigencia (Desde - Hasta)</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while($t = $resultado_tarifas->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800"><?php echo $t['Marca'] . " " . $t['Modelo']; ?></span>
                                <span class="text-xs text-gray-500 block"><?php echo $t['Placa']; ?></span>
                            </td>
                            <td class="px-6 py-4 text-blue-900 font-bold">$<?php echo $t['Monto_Diario']; ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo date('d/m/Y', strtotime($t['Fecha_Inicio'])); ?> al <?php echo date('d/m/Y', strtotime($t['Fecha_Fin'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                $hoy = date('Y-m-d');
                                $activa = ($hoy >= $t['Fecha_Inicio'] && $hoy <= $t['Fecha_Fin']);
                                $futura = ($t['Fecha_Inicio'] > $hoy);
                                $pasada = ($t['Fecha_Fin'] < $hoy);
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo $activa ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'; ?>">
                                    <?php echo $activa ? 'Activa' : ($futura ? 'Futura' : 'Vencida'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if($futura): ?>
                                    <a href="editar_tarifa.php?id=<?php echo $t['ID_Tarifa']; ?>" class="text-blue-600 hover:text-blue-900 mx-1 inline-block" title="Editar Tarifa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-300 mx-1 inline-block cursor-not-allowed" title="No modificable (Activa o Pasada)">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </span>
                                <?php endif; ?>

                                <?php if($pasada): ?>
                                    <a href="../../controllers/eliminar_tarifa.php?id=<?php echo $t['ID_Tarifa']; ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar esta tarifa?');" class="text-red-400 hover:text-red-700 mx-1 inline-block" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-300 mx-1 inline-block cursor-not-allowed" title="No se puede eliminar (Activa o Futura)">
                                        <i class="fa-solid fa-trash"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php if($total_paginas > 1): ?>
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
                    <span class="text-sm text-gray-500">Mostrando página <span class="font-bold"><?php echo $pagina_actual; ?></span> de <span class="font-bold"><?php echo $total_paginas; ?></span></span>
                    <div class="flex items-center gap-1">
                        <?php if($pagina_actual > 1): ?>
                            <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="px-3 py-1 bg-white border border-gray-200 rounded text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="?pagina=<?php echo $i; ?>" class="px-3 py-1 border border-gray-200 rounded text-sm transition-colors <?php echo $pagina_actual == $i ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($pagina_actual < $total_paginas): ?>
                            <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="px-3 py-1 bg-white border border-gray-200 rounded text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
</body>
</html>