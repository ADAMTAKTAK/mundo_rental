<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Traemos la lista de vehículos para el selector
$vehiculos = $connection->query("SELECT ID_Vehiculo, Marca, Modelo, Placa FROM vehiculos ORDER BY Marca");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Tarifa | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 p-6 text-white text-center">
            <h2 class="text-xl font-bold">Asignar Precio</h2>
        </div>

        <form action="../../controllers/tarifa_controller.php" method="POST" class="p-8 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Seleccionar Vehículo</label>
                <select name="id_vehiculo" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                    <?php while($v = $vehiculos->fetch_assoc()): ?>
                        <option value="<?php echo $v['ID_Vehiculo']; ?>">
                            <?php echo $v['Marca'] . " " . $v['Modelo'] . " (" . $v['Placa'] . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Monto Diario ($)</label>
                <input name="monto" required type="number" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="0.00">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Desde</label>
                    <input name="fecha_inicio" required type="date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hasta</label>
                    <input name="fecha_fin" required type="date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="tarifas.php" class="flex-1 text-center py-3 border border-gray-300 text-gray-600 font-bold rounded-lg hover:bg-gray-50 transition-all">Cancelar</a>
                <button type="submit" class="flex-1 py-3 bg-blue-900 text-white font-bold rounded-lg hover:bg-blue-800 shadow-lg transition-all">Guardar</button>
            </div>
        </form>
    </div>
</body>
</html>