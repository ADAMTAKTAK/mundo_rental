<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' || !isset($_GET['id'])) {
    header("Location: clientes.php");
    exit();
}

$id = (int)$_GET['id'];
$query = "SELECT c.*, u.Username, u.ID_Usuario, u.Rol FROM clientes c LEFT JOIN usuarios u ON c.ID_Cliente = u.ID_Cliente WHERE c.ID_Cliente = $id";
$cliente = $connection->query($query)->fetch_assoc();

if (!$cliente) {
    header("Location: clientes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-10">

    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 px-8 py-6 flex justify-between items-center text-white">
            <h2 class="text-xl font-bold uppercase"><i class="fa-solid fa-user-pen mr-2"></i> Editar Cliente</h2>
            <a href="clientes.php" class="hover:text-blue-200 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>

        <div class="p-8">
            <form action="../../controllers/editar_cliente_controller.php" method="POST" class="space-y-6">
                <input type="hidden" name="id_cliente" value="<?php echo $cliente['ID_Cliente']; ?>">
                <input type="hidden" name="id_usuario_existente" value="<?php echo $cliente['ID_Usuario'] ?? ''; ?>">

                <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest border-b pb-2 mb-4">Datos Legales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nombre</label>
                        <input name="nombre" required type="text" value="<?php echo $cliente['Nombre']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Apellido</label>
                        <input name="apellido" required type="text" value="<?php echo $cliente['Apellido']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    
                    <div class="flex gap-2">
                        <div class="w-1/3">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                            <select name="tipo_doc" class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-900">
                                <option value="V" <?php echo $cliente['Tipo_Documento'] == 'V' ? 'selected' : ''; ?>>V</option>
                                <option value="E" <?php echo $cliente['Tipo_Documento'] == 'E' ? 'selected' : ''; ?>>E</option>
                                <option value="P" <?php echo $cliente['Tipo_Documento'] == 'P' ? 'selected' : ''; ?>>Pasaporte</option>
                            </select>
                        </div>
                        <div class="w-2/3">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Documento</label>
                            <input name="num_doc" required type="text" value="<?php echo $cliente['Numero_Documento']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Teléfono</label>
                        <input name="telefono" required type="text" value="<?php echo $cliente['Telefono']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Licencia de Conducir</label>
                        <input name="licencia" required type="text" value="<?php echo $cliente['Licencia_Conducir']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900 uppercase">
                    </div>
                     <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                        <input name="email" type="email" value="<?php echo $cliente['Email']; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                </div>

                <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest border-b pb-2 mb-4">Acceso Web</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Usuario</label>
                        <input name="username" type="text" value="<?php echo $cliente['Username'] ?? ''; ?>" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nueva Contraseña</label>
                        <input name="password" type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900" placeholder="Dejar en blanco para no cambiar">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nivel de Permisos</label>
                        <div class="w-full px-4 py-2 border rounded-lg bg-gray-50 text-gray-500 font-semibold cursor-not-allowed flex items-center justify-between">
                            <span><?php echo $cliente['Rol'] ?? 'Cliente Físico'; ?></span>
                            <?php if(isset($cliente['Rol']) && $cliente['Rol'] == 'Admin'): ?>
                                <i class="fa-solid fa-shield-halved text-blue-900"></i>
                            <?php endif; ?>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">El nivel de sistema no es modificable aquí.</p>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-100">
                    <a href="clientes.php" class="flex-1 text-center py-4 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</a>
                    <button type="submit" class="flex-1 py-4 bg-blue-900 text-white font-bold rounded-xl shadow-lg hover:bg-blue-800 transition-colors uppercase">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>