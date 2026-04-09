<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT * FROM usuarios WHERE ID_Usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// NUEVO ESCUDO: Si no encuentra los datos, la sesión está corrupta. Lo deslogueamos.
if (!$user_data) {
    header("Location: ../../controllers/logout_controller.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | Mundo Rental</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
        <div class="bg-blue-900 px-8 py-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">Mi Perfil</h2>
                <p class="text-blue-200 text-sm">Gestiona tu información</p>
            </div>
            <a href="../../index.php" class="text-white hover:text-blue-200">
                <i class="fa-solid fa-house"></i>
            </a>
        </div>

        <div class="p-8">
            <?php if(isset($_GET['success'])): ?>
                <div class="bg-green-50 text-green-700 p-4 mb-6 rounded-md text-sm border-l-4 border-green-500">
                    <i class="fa-solid fa-circle-check mr-2"></i> ¡Perfil actualizado con éxito!
                </div>
            <?php endif; ?>

            <form action="../../controllers/profile_controller.php" method="POST" class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Nombre</label>
                        <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" 
                               name="first_name" required type="text" value="<?php echo $user_data['Nombre']; ?>"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Apellido</label>
                        <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" 
                               name="last_name" required type="text" value="<?php echo $user_data['Apellido']; ?>"/>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Nombre de Usuario</label>
                    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" 
                           name="username" required type="text" value="<?php echo $user_data['Username']; ?>"/>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Nueva Contraseña</label>
                    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 outline-none" 
                           name="password" type="password" placeholder="Dejar en blanco para no cambiar"/>
                </div>

                <button type="submit" class="w-full py-4 mt-6 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</body>
</html>