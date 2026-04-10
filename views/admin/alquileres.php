<?php
session_start();
require_once '../../config/database_connection.php';

// Seguridad: Solo Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// LÓGICA DE PAGINACIÓN
$res_total = $connection->query("SELECT COUNT(*) as total FROM alquileres");
$total_contratos = $res_total->fetch_assoc()['total'];
$registros_por_pagina = 10;
$total_paginas = ceil($total_contratos / $registros_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = max(0, ($pagina_actual - 1) * $registros_por_pagina);

// CONSULTA DE CONTRATOS (Uniendo Alquileres, Clientes y Vehículos)
$query_contratos = "
    SELECT a.ID_Alquiler, a.Fecha_Salida, a.Fecha_Devolucion_Prevista, a.Monto_Total, a.Estado,
           c.Nombre AS ClienteNombre, c.Apellido AS ClienteApellido, c.Tipo_Documento, c.Numero_Documento,
           v.Marca, v.Modelo, v.Placa
    FROM alquileres a
    JOIN clientes c ON a.ID_Cliente = c.ID_Cliente
    JOIN vehiculos v ON a.ID_Vehiculo = v.ID_Vehiculo
    ORDER BY a.Fecha_Salida DESC 
    LIMIT $offset, $registros_por_pagina
";
$contratos = $connection->query($query_contratos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Alquileres | Admin</title>
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

    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="h-20 bg-white shadow-sm flex items-center justify-between px-8 sticky top-0 z-10">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Contratos</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600">Admin: <?php echo $_SESSION['username']; ?></span>
                <a href="../../controllers/logout_controller.php" class="text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-power-off text-lg"></i>
                </a>
            </div>
        </header>

        <div class="p-8 flex-1">
            <div class="flex justify-between items-center mb-8">
                <p class="text-gray-500">Administra las reservas web y los alquileres presenciales.</p>
                <a href="nuevo_alquiler.php" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-user-tie"></i> Alquilar en Oficina
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold">N° Contrato</th>
                            <th class="px-6 py-4 font-semibold">Cliente</th>
                            <th class="px-6 py-4 font-semibold">Vehículo</th>
                            <th class="px-6 py-4 font-semibold">Período</th>
                            <th class="px-6 py-4 font-semibold text-right">Total</th>
                            <th class="px-6 py-4 font-semibold text-center">Estado</th>
                            <th class="px-6 py-4 font-semibold text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php while($c = $contratos->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-gray-800">#<?php echo str_pad($c['ID_Alquiler'], 5, "0", STR_PAD_LEFT); ?></td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800 block"><?php echo $c['ClienteNombre'] . ' ' . $c['ClienteApellido']; ?></span>
                                <span class="text-xs text-gray-500"><?php echo $c['Tipo_Documento'].'-'.$c['Numero_Documento']; ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800 block"><?php echo $c['Marca'] . ' ' . $c['Modelo']; ?></span>
                                <span class="text-xs text-gray-500 font-mono"><?php echo $c['Placa']; ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($c['Fecha_Salida'])); ?> <br> 
                                <span class="text-xs text-gray-400">hasta</span> <br> 
                                <?php echo date('d/m/Y H:i', strtotime($c['Fecha_Devolucion_Prevista'])); ?>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-blue-900">
                                $<?php echo number_format($c['Monto_Total'], 2); ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php 
                                    $bg = 'bg-gray-100 text-gray-600';
                                    if($c['Estado'] == 'Reservado') $bg = 'bg-blue-100 text-blue-700';
                                    if($c['Estado'] == 'En Curso') $bg = 'bg-orange-100 text-orange-700';
                                    if($c['Estado'] == 'Finalizado') $bg = 'bg-green-100 text-green-700';
                                    if($c['Estado'] == 'Cancelado') $bg = 'bg-red-100 text-red-700';
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $bg; ?>">
                                    <?php echo $c['Estado']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="ver_alquiler.php?id=<?php echo $c['ID_Alquiler']; ?>" 
                                    class="text-gray-400 hover:text-blue-900 transition-colors inline-block transform hover:scale-110" 
                                    title="Ver Detalles del Contrato">
                                    <i class="fa-solid fa-eye text-lg"></i>
                                </a>

                                <?php if($c['Estado'] == 'Reservado'): ?>
                                    <a href="../../controllers/cambiar_estado_alquiler.php?id=<?php echo $c['ID_Alquiler']; ?>&accion=entregar" 
                                        class="text-orange-500 hover:text-orange-700 transition-colors inline-block transform hover:scale-110" 
                                        title="Entregar Llaves (Pasar a En Curso)">
                                        <i class="fa-solid fa-key text-lg"></i>
                                    </a>
                                <?php elseif($c['Estado'] == 'En Curso'): ?>
                                    <a href="../../controllers/cambiar_estado_alquiler.php?id=<?php echo $c['ID_Alquiler']; ?>&accion=finalizar" 
                                        onclick="return confirm('¿Confirmas que el vehículo fue devuelto?');"
                                        class="text-green-500 hover:text-green-700 transition-colors inline-block transform hover:scale-110" 
                                        title="Recibir Vehículo (Finalizar Contrato)">
                                        <i class="fa-solid fa-flag-checkered text-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($total_paginas > 1): ?>
            <?php endif; ?>

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
    </main>
</body>
</html>