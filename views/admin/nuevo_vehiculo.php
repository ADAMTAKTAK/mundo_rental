<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Vehículo | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-10">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 px-8 py-6 flex justify-between items-center">
            <h2 class="text-xl font-bold text-white"><i class="fa-solid fa-car-side mr-2"></i> Registrar Nuevo Vehículo</h2>
            <a href="dashboard.php" class="text-blue-200 hover:text-white transition-colors"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>

        <div class="p-8">
            <form action="../../controllers/vehiculo_controller.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Marca</label>
                        <input name="marca" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Ej. Toyota">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Modelo</label>
                        <input name="modelo" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Ej. Yaris">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Placa</label>
                        <input name="placa" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none uppercase" placeholder="ABC-123">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Año</label>
                        <input name="anio" required type="number" min="2000" max="2030" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="2024">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Color</label>
                        <input name="color" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Blanco">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Capacidad (Pasajeros)</label>
                        <input name="capacidad" required type="number" min="1" max="15" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Categoría</label>
                    <select name="id_categoria" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                        <option value="1">Económicos</option>
                        <option value="2">Camionetas Grandes</option>
                        <option value="3">Gama Alta</option>
                    </select>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <label class="block text-xs font-semibold text-gray-500 mb-3 uppercase">Fotografía del Vehículo</label>
                    <input type="file" name="foto_carro" accept="image/*" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-900 hover:file:bg-blue-100 cursor-pointer">
                </div>

                <button type="submit" class="w-full py-4 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-lg transition-all">
                    Guardar Vehículo
                </button>
            </form>
        </div>
    </div>
</body>
</html>