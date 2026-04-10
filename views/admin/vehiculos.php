<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Mensajes de éxito o error para las acciones de eliminar/editar
$mensaje = '';
if(isset($_GET['success']) && $_GET['success'] == 'eliminado') {
    $mensaje = '<div class="bg-green-100 text-green-700 p-4 mb-6 rounded-lg text-sm font-semibold"><i class="fa-solid fa-check mr-2"></i>Vehículo eliminado de la flota con éxito.</div>';
}
if(isset($_GET['error']) && $_GET['error'] == 'en_uso') {
    $mensaje = '<div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm font-semibold"><i class="fa-solid fa-triangle-exclamation mr-2"></i>No se puede eliminar el vehículo porque ya tiene contratos de alquiler o tarifas asociadas en el historial. Intenta cambiar su estado a Mantenimiento.</div>';
}

// Consulta de toda la flota unida con categorías
$query = "SELECT v.*, c.Nombre as Categoria 
          FROM vehiculos v 
          LEFT JOIN categorias c ON v.ID_Categoria = c.ID_Categoria 
          ORDER BY v.Marca ASC";
$resultado = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Flota | Admin</title>
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
            <h1 class="text-2xl font-bold text-gray-800">Inventario de Vehículos</h1>
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
                <p class="text-gray-500">Administra todos los vehículos registrados en tu agencia.</p>
                <a href="nuevo_vehiculo.php" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Añadir Carro
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($v = $resultado->fetch_assoc()): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-40 bg-gray-100 overflow-hidden">
                        <img src="../../<?php echo $v['Imagen_URL']; ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-gray-800"><?php echo $v['Marca'].' '.$v['Modelo']; ?></h3>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-gray-100 text-gray-500 uppercase border border-gray-200"><?php echo $v['Placa']; ?></span>
                        </div>
                        <p class="text-xs text-gray-400 mb-4"><?php echo $v['Categoria']; ?> • <?php echo $v['Anio']; ?></p>
                        
                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                            <?php 
                                $color = $v['Estado'] == 'Disponible' ? 'text-green-700 bg-green-100' : ($v['Estado'] == 'Alquilado' ? 'text-orange-700 bg-orange-100' : 'text-red-700 bg-red-100');
                            ?>
                            <span class="text-[10px] font-bold px-3 py-1 rounded-full <?php echo $color; ?>">
                                <?php echo $v['Estado']; ?>
                            </span>
                            
                            <div class="flex gap-3">
                                <a href="editar_vehiculo.php?id=<?php echo $v['ID_Vehiculo']; ?>" class="text-blue-500 hover:text-blue-800 transition-colors" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                
                                <a href="../../controllers/eliminar_vehiculo.php?id=<?php echo $v['ID_Vehiculo']; ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este vehículo?');" class="text-red-400 hover:text-red-700 transition-colors" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</body>
</html>