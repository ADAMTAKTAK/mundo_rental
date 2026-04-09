<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | Mundo Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-10">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 px-8 py-6 text-center">
            <h2 class="text-2xl font-bold text-white"><i class="fa-solid fa-user-plus mr-2"></i> Crear Cuenta</h2>
            <p class="text-blue-200 text-sm mt-1">Completa tus datos para poder reservar vehículos</p>
        </div>

        <div class="p-8">
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 text-red-700 p-4 mb-6 rounded-md text-sm border-l-4 border-red-500">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> 
                    <?php 
                        if($_GET['error'] == 'usuario_existe') echo "El nombre de usuario o email ya está en uso.";
                        else if($_GET['error'] == 'documento_existe') echo "Ese documento de identidad ya está registrado.";
                        else echo "Ocurrió un error. Inténtalo de nuevo."; 
                    ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/register_controller.php" method="POST" class="space-y-6">
                
                <h3 class="font-bold text-gray-700 border-b pb-2">1. Datos de la Cuenta</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Usuario</label>
                        <input name="username" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Contraseña</label>
                        <input name="password" required type="password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Email</label>
                        <input name="email" required type="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                    </div>
                </div>

                <h3 class="font-bold text-gray-700 border-b pb-2 mt-6">2. Datos Personales (Para el Contrato)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Nombre</label>
                        <input name="nombre" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Apellido</label>
                        <input name="apellido" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none">
                    </div>
                    
                    <div class="flex gap-2">
                        <div class="w-1/3">
                            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Tipo</label>
                            <select name="tipo_doc" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="P">Pasaporte</option>
                            </select>
                        </div>
                        <div class="w-2/3">
                            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Documento</label>
                            <input name="num_doc" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Ej. 28123456">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Teléfono</label>
                        <input name="telefono" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Ej. 0414-1234567">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Licencia de Conducir (Nro)</label>
                        <input name="licencia" required type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Ej. LIC-28123456">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 mt-4 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-lg transition-all">
                    Completar Registro
                </button>
                
                <p class="text-center text-sm text-gray-500 mt-4">
                    ¿Ya tienes cuenta? <a href="login.php" class="text-blue-900 font-bold hover:underline">Inicia Sesión aquí</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>