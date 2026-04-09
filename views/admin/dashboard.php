<?php
session_start();
require_once '../../config/database_connection.php';

// 1. SEGURIDAD EXTREMA
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// 2. CONSULTAS DE MÉTRICAS RÁPIDAS
$res_total = $connection->query("SELECT COUNT(*) as total FROM vehiculos");
$total_vehiculos = $res_total->fetch_assoc()['total'];

$res_alquilados = $connection->query("SELECT COUNT(*) as alquilados FROM vehiculos WHERE Estado = 'Alquilado'");
$vehiculos_alquilados = $res_alquilados->fetch_assoc()['alquilados'];

$vehiculos_disponibles = $total_vehiculos - $vehiculos_alquilados;

// 3. LÓGICA DE PAGINACIÓN PARA EL INVENTARIO
$registros_por_pagina = 5; // Cantidad de carros por página
$total_paginas = ceil($total_vehiculos / $registros_por_pagina);

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
if ($pagina_actual > $total_paginas && $total_paginas > 0) $pagina_actual = $total_paginas;

$offset = max(0, ($pagina_actual - 1) * $registros_por_pagina);

// 4. CONSULTA PARA LA TABLA DE INVENTARIO (CON LIMIT)
$query_inventario = "SELECT * FROM vehiculos ORDER BY Marca, Modelo LIMIT $offset, $registros_por_pagina";
$inventario = $connection->query($query_inventario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Mundo Rental</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden"> <aside class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0"> <div class="h-20 flex items-center justify-center border-b border-blue-800">
            <i class="fa-solid fa-car text-2xl mr-2"></i>
            <span class="font-bold text-lg tracking-widest">ADMIN PANEL</span>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 bg-blue-800 px-4 py-3 rounded-lg text-white font-medium">
                <i class="fa-solid fa-chart-pie w-5"></i> Resumen
            </a>
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-200 hover:bg-blue-800 hover:text-white transition-colors">
                <i class="fa-solid fa-car-side w-5"></i> Mi Flota
            </a>
            <a href="tarifas.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-200 hover:bg-blue-800 hover:text-white transition-colors">
                <i class="fa-solid fa-tags w-5"></i> Tarifas
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-200 hover:bg-blue-800 hover:text-white transition-colors">
                <i class="fa-solid fa-file-invoice-dollar w-5"></i> Contratos (Alquileres)
            </a>
        </nav>
        <div class="p-4 border-t border-blue-800">
            <a href="../../index.php" class="flex items-center gap-3 text-blue-200 hover:text-white transition-colors text-sm">
                <i class="fa-solid fa-arrow-left"></i> Volver a la Web
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-y-auto"> <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 sticky top-0 z-10">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard General</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600">Admin: <?php echo $_SESSION['username']; ?></span>
                <a href="../../controllers/logout_controller.php" class="text-red-500 hover:text-red-700" title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off text-lg"></i>
                </a>
            </div>
        </header>

        <div class="p-8 flex-1">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-50 text-blue-900 rounded-full flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase">Total Flota</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_vehiculos; ?></p>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 bg-green-50 text-green-600 rounded-full flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase">Disponibles</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $vehiculos_disponibles; ?></p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase">Alquilados</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $vehiculos_alquilados; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Inventario Actual</h2>
                    <a href="nuevo_vehiculo.php" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-md flex items-center gap-2 inline-flex">
                        <i class="fa-solid fa-plus"></i> Nuevo Vehículo
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Vehículo</th>
                                <th class="px-6 py-4 font-semibold">Placa</th>
                                <th class="px-6 py-4 font-semibold">Año / Color</th>
                                <th class="px-6 py-4 font-semibold">Estado</th>
                                <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($vehiculo = $inventario->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md overflow-hidden bg-gray-200">
                                            <img src="../../<?php echo $vehiculo['Imagen_URL']; ?>" class="w-full h-full object-cover" alt="Auto">
                                        </div>
                                        <div class="font-bold text-gray-800"><?php echo $vehiculo['Marca'] . ' ' . $vehiculo['Modelo']; ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $vehiculo['Placa']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $vehiculo['Anio'] . ' • ' . $vehiculo['Color']; ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $color_estado = $vehiculo['Estado'] == 'Disponible' ? 'bg-green-100 text-green-700' : 
                                                        ($vehiculo['Estado'] == 'Alquilado' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700');
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $color_estado; ?>">
                                        <?php echo $vehiculo['Estado']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-blue-600 hover:text-blue-900 mx-1" title="Editar"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <a href="tarifas.php" class="text-gray-400 hover:text-gray-600 mx-1" title="Gestionar Tarifas">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

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