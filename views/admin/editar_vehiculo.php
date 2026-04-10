<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' || !isset($_GET['id'])) {
    header("Location: vehiculos.php");
    exit();
}

$id = (int)$_GET['id'];
$vehiculo = $connection->query("SELECT * FROM vehiculos WHERE ID_Vehiculo = $id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Vehículo | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-10">
    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 px-8 py-6 flex justify-between items-center text-white">
            <h2 class="text-xl font-bold uppercase"><i class="fa-solid fa-pen mr-2"></i> Editar Vehículo</h2>
            <a href="vehiculos.php" class="hover:text-blue-200"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>
        <div class="p-8">
            
            <?php if(isset($_GET['error']) && $_GET['error'] == 'db'): ?>
                <div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm font-semibold">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Ocurrió un error al guardar en la base de datos.
                </div>
            <?php endif; ?>

            <form action="../../controllers/editar_vehiculo_controller.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="id_vehiculo" value="<?php echo $vehiculo['ID_Vehiculo']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Marca</label>
                        <input name="marca" required type="text" value="<?php echo $vehiculo['Marca']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Modelo</label>
                        <input name="modelo" required type="text" value="<?php echo $vehiculo['Modelo']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Placa</label>
                        <input name="placa" required type="text" value="<?php echo $vehiculo['Placa']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Año</label>
                        <input name="anio" required type="number" min="2000" max="2030" value="<?php echo $vehiculo['Anio']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Color</label>
                        <input name="color" required type="text" value="<?php echo $vehiculo['Color']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Capacidad (Pasajeros)</label>
                        <input name="capacidad" required type="number" min="1" max="15" value="<?php echo $vehiculo['Capacidad']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Categoría</label>
                        <select name="id_categoria" required class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-900">
                            <option value="1" <?php echo $vehiculo['ID_Categoria'] == 1 ? 'selected' : ''; ?>>Económicos</option>
                            <option value="2" <?php echo $vehiculo['ID_Categoria'] == 2 ? 'selected' : ''; ?>>Camionetas Grandes</option>
                            <option value="3" <?php echo $vehiculo['ID_Categoria'] == 3 ? 'selected' : ''; ?>>Gama Alta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Estado Físico</label>
                        <select name="estado" class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-900">
                            <option value="Disponible" <?php echo $vehiculo['Estado'] == 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                            <option value="Alquilado" <?php echo $vehiculo['Estado'] == 'Alquilado' ? 'selected' : ''; ?>>Alquilado</option>
                            <option value="Mantenimiento" <?php echo $vehiculo['Estado'] == 'Mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                    </div>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mt-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-3">Actualizar Fotografía (Opcional)</label>
                    <div class="flex items-center gap-4 justify-center">
                        <img src="../../<?php echo $vehiculo['Imagen_URL']; ?>" class="w-16 h-16 rounded object-cover shadow border">
                        <input type="file" name="foto_carro" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-900 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2">Si dejas esto vacío, se mantendrá la imagen actual.</p>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-100">
                    <a href="vehiculos.php" class="flex-1 text-center py-4 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</a>
                    <button type="submit" class="flex-1 py-4 bg-blue-900 text-white font-bold rounded-xl shadow-lg hover:bg-blue-800 transition-colors uppercase">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>