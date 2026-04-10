<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$mensaje = '';
if(isset($_GET['success']) && $_GET['success'] == 'eliminado') {
    $mensaje = '<div class="bg-green-100 text-green-700 p-4 mb-6 rounded-lg text-sm font-semibold"><i class="fa-solid fa-check mr-2"></i>Cliente y su cuenta de usuario eliminados con éxito.</div>';
}
if(isset($_GET['error']) && $_GET['error'] == 'en_uso') {
    $mensaje = '<div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm font-semibold"><i class="fa-solid fa-triangle-exclamation mr-2"></i>No se puede eliminar el cliente porque tiene contratos de alquiler asociados en el historial.</div>';
}
if(isset($_GET['error']) && $_GET['error'] == 'auto_eliminacion') {
    $mensaje = '<div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm font-semibold"><i class="fa-solid fa-shield-halved mr-2"></i>Por seguridad, el sistema no permite que elimines tu propia cuenta administrativa.</div>';
}

$res_total = $connection->query("SELECT COUNT(*) as total FROM clientes");
$total_clientes = $res_total->fetch_assoc()['total'];
$registros_por_pagina = 10;
$total_paginas = ceil($total_clientes / $registros_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
if ($pagina_actual > $total_paginas && $total_paginas > 0) $pagina_actual = $total_paginas;
$offset = max(0, ($pagina_actual - 1) * $registros_por_pagina);

$query = "
    SELECT c.*, u.Username, u.Rol, u.ID_Usuario 
    FROM clientes c 
    LEFT JOIN usuarios u ON c.ID_Cliente = u.ID_Cliente 
    ORDER BY c.Nombre ASC 
    LIMIT $offset, $registros_por_pagina
";
$resultado = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes | Admin</title>
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
            <h1 class="text-2xl font-bold text-gray-800">Directorio de Clientes</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600">Admin: <?php echo $_SESSION['username']; ?></span>
                <a href="../../controllers/logout_controller.php" class="text-red-500 hover:text-red-700" title="Cerrar Sesión">
                    <i class="fa-solid fa-power-off text-lg"></i>
                </a>
            </div>
        </header>

        <div class="p-8 flex-1">
            
            <?php echo $mensaje; ?>

            <div class="flex justify-between items-center mb-8">
                <p class="text-gray-500">Administra los datos legales y cuentas de acceso de tus clientes.</p>
                <a href="nuevo_cliente.php" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Añadir Cliente
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Cliente</th>
                                <th class="px-6 py-4 font-semibold">Contacto</th>
                                <th class="px-6 py-4 font-semibold">Licencia</th>
                                <th class="px-6 py-4 font-semibold">Cuenta Web</th>
                                <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($c = $resultado->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800"><?php echo $c['Nombre'] . ' ' . $c['Apellido']; ?></div>
                                    <div class="text-xs text-gray-500 mt-1"><?php echo $c['Tipo_Documento'] . '-' . $c['Numero_Documento']; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600"><i class="fa-solid fa-phone w-4 text-gray-400"></i> <?php echo $c['Telefono']; ?></div>
                                    <div class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-envelope w-4 text-gray-400"></i> <?php echo $c['Email']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                    <?php echo $c['Licencia_Conducir']; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($c['Username']): ?>
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-md border border-blue-100">
                                            @<?php echo $c['Username']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Sin cuenta</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="editar_cliente.php?id=<?php echo $c['ID_Cliente']; ?>" class="text-blue-600 hover:text-blue-900 mx-1 inline-block" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <?php if ($c['ID_Usuario'] == $_SESSION['user_id']): ?>
                                        <span class="text-gray-300 mx-1 inline-block cursor-not-allowed" title="No puedes eliminar tu propia cuenta">
                                            <i class="fa-solid fa-trash"></i>
                                        </span>
                                    <?php else: ?>
                                        <a href="../../controllers/eliminar_cliente.php?id=<?php echo $c['ID_Cliente']; ?>" onclick="return confirm('¿Eliminar definitivamente a este cliente?');" class="text-red-400 hover:text-red-700 mx-1 inline-block" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
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
                            <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="px-3 py-1 bg-white border border-gray-200 rounded text-sm text-gray-600 hover:bg-gray-50"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="?pagina=<?php echo $i; ?>" class="px-3 py-1 border border-gray-200 rounded text-sm <?php echo $pagina_actual == $i ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        <?php if($pagina_actual < $total_paginas): ?>
                            <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="px-3 py-1 bg-white border border-gray-200 rounded text-sm text-gray-600 hover:bg-gray-50"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
</body>
</html>