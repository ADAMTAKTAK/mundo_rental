<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Cliente | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-10">

    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 px-8 py-6 flex justify-between items-center text-white">
            <h2 class="text-xl font-bold uppercase"><i class="fa-solid fa-user-plus mr-2"></i> Registrar Cliente</h2>
            <a href="clientes.php" class="hover:text-blue-200 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>

        <div class="p-8">
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm font-semibold border-l-4 border-red-500">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> 
                    <?php 
                        if($_GET['error'] == 'duplicado') echo "El documento de identidad o el nombre de usuario ya existen en el sistema.";
                        else echo "Ocurrió un error en la base de datos.";
                    ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/nuevo_cliente_controller.php" method="POST" class="space-y-6">
                
                <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest border-b pb-2 mb-4">1. Datos Legales (Contrato)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nombre</label>
                        <input name="nombre" required type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Apellido</label>
                        <input name="apellido" required type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    
                    <div class="flex gap-2">
                        <div class="w-1/3">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipo</label>
                            <select name="tipo_doc" class="w-full px-4 py-2 border rounded-lg bg-white outline-none focus:ring-2 focus:ring-blue-900">
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="P">Pasaporte</option>
                            </select>
                        </div>
                        <div class="w-2/3">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Documento</label>
                            <input name="num_doc" required type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Teléfono</label>
                        <input name="telefono" required type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                        <input name="email" type="email" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Licencia de Conducir</label>
                        <input name="licencia" required type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900 uppercase">
                    </div>
                </div>

                <h3 class="text-xs font-black text-blue-900 uppercase tracking-widest border-b pb-2 mb-4">2. Acceso Web (Opcional)</h3>
                <p class="text-[10px] text-gray-400 mb-4">Si llenas estos campos, el cliente podrá iniciar sesión en la web para reservar. Si los dejas vacíos, solo existirá para contratos físicos en oficina.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Usuario</label>
                        <input name="username" type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900" placeholder="Ej. jperez">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Contraseña</label>
                        <input name="password" type="text" class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-900" placeholder="Asignar clave">
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-100">
                    <a href="clientes.php" class="flex-1 text-center py-4 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</a>
                    <button type="submit" class="flex-1 py-4 bg-blue-900 text-white font-bold rounded-xl shadow-lg hover:bg-blue-800 transition-colors uppercase">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>