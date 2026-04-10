<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' || !isset($_GET['id'])) {
    header("Location: tarifas.php");
    exit();
}

$id_tarifa = (int)$_GET['id'];
$tarifa = $connection->query("SELECT * FROM tarifas WHERE ID_Tarifa = $id_tarifa")->fetch_assoc();

if (!$tarifa) {
    header("Location: tarifas.php");
    exit();
}

$hoy = date('Y-m-d');
if ($tarifa['Fecha_Inicio'] <= $hoy) {
    header("Location: tarifas.php?error=editar_activa");
    exit();
}

$vehiculos = $connection->query("SELECT ID_Vehiculo, Marca, Modelo, Placa FROM vehiculos ORDER BY Marca");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Tarifa | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 p-6 text-white text-center">
            <h2 class="text-xl font-bold">Editar Tarifa</h2>
        </div>

        <?php if(isset($_GET['error'])): ?>
                <div class="px-8 pt-4 pb-0">
                    <div class="bg-red-100 text-red-700 p-4 rounded-lg text-sm font-semibold border-l-4 border-red-500">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i> 
                        <?php 
                            if($_GET['error'] == 'choque_fechas') echo "El vehículo ya tiene una tarifa asignada que choca con estas fechas.";
                            if($_GET['error'] == 'fechas_incoherentes') echo "La fecha de inicio no puede ser mayor que la fecha de fin.";
                        ?>
                    </div>
                </div>
            <?php endif; ?>

        <form action="../../controllers/editar_tarifa_controller.php" method="POST" class="p-8 space-y-5">
            <input type="hidden" name="id_tarifa" value="<?php echo $tarifa['ID_Tarifa']; ?>">
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Vehículo Asignado</label>
                <select name="id_vehiculo" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                    <?php while($v = $vehiculos->fetch_assoc()): ?>
                        <option value="<?php echo $v['ID_Vehiculo']; ?>" <?php echo $v['ID_Vehiculo'] == $tarifa['ID_Vehiculo'] ? 'selected' : ''; ?>>
                            <?php echo $v['Marca'] . " " . $v['Modelo'] . " (" . $v['Placa'] . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Monto Diario ($)</label>
                <input name="monto" required type="number" step="0.01" value="<?php echo $tarifa['Monto_Diario']; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Desde</label>
                    <input name="fecha_inicio" required type="date" value="<?php echo $tarifa['Fecha_Inicio']; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hasta</label>
                    <input name="fecha_fin" required type="date" value="<?php echo $tarifa['Fecha_Fin']; ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="tarifas.php" class="flex-1 text-center py-3 border border-gray-300 text-gray-600 font-bold rounded-lg hover:bg-gray-50 transition-all">Cancelar</a>
                <button type="submit" class="flex-1 py-3 bg-blue-900 text-white font-bold rounded-lg hover:bg-blue-800 shadow-lg transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>