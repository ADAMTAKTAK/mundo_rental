<?php
session_start();

require_once 'config/database_connection.php';

$is_logged_in = isset($_SESSION['user_id']);
$role = $is_logged_in ? $_SESSION['role'] : null;
$username = $is_logged_in ? $_SESSION['username'] : '';

$is_valid_client = false;
if ($is_logged_in && $role === 'Cliente') {
    $stmt_check = $connection->prepare("SELECT ID_Cliente FROM usuarios WHERE ID_Usuario = ?");
    $stmt_check->bind_param("i", $_SESSION['user_id']);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result()->fetch_assoc();
    if (!empty($res_check['ID_Cliente'])) {
        $is_valid_client = true;
    }
}

$query_vehiculos = "
    SELECT v.*, 
           (SELECT Monto_Diario FROM tarifas t 
            WHERE t.ID_Vehiculo = v.ID_Vehiculo 
            AND CURDATE() BETWEEN t.Fecha_Inicio AND t.Fecha_Fin 
            LIMIT 1) as Precio_Dia
    FROM vehiculos v 
    WHERE v.Estado = 'Disponible'
";
$resultado_vehiculos = $connection->query($query_vehiculos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundo Rental Margarita | Inicio</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-car text-blue-900 text-3xl"></i>
                    <span class="font-bold text-xl text-blue-900 tracking-wide uppercase">Mundo Rental</span>
                </div>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="#" class="text-blue-900 font-semibold border-b-2 border-blue-900 py-2">Inicio</a>
                    <a href="#flota" class="text-gray-500 hover:text-blue-900 py-2 transition-colors">Nuestra Flota</a>
                    <a href="#servicios" class="text-gray-500 hover:text-blue-900 py-2 transition-colors">Servicios</a>
                    <a href="#contacto" class="text-gray-500 hover:text-blue-900 py-2 transition-colors">Contacto</a>
                </nav>

                <div class="flex items-center gap-4">
                    <?php if (!$is_logged_in): ?>
                        <a href="views/auth/login.php" class="text-sm font-semibold text-blue-900 bg-blue-50 hover:bg-blue-100 px-5 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                            <i class="fa-regular fa-circle-user"></i>
                            Sign In / Register
                        </a>
                    <?php else: ?>
                        <div class="flex items-center gap-2 mr-2">
                            <a href="views/auth/profile.php" class="text-sm font-medium text-gray-700 hover:text-blue-900 transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-user-gear text-gray-400"></i>
                                <span><?php echo htmlspecialchars($username); ?></span>
                            </a>
                        </div>
                        <?php if ($role === 'Admin'): ?>
                            <a href="views/admin/dashboard.php" class="text-sm font-semibold text-white bg-blue-900 hover:bg-blue-800 px-5 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-gauge-high"></i>
                                Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="controllers/logout_controller.php" class="text-gray-400 hover:text-red-600 transition-colors p-2" title="Cerrar Sesión">
                            <i class="fa-solid fa-right-from-bracket text-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow relative">

        <div class="absolute top-4 left-0 right-0 z-50 flex flex-col items-center gap-2 pointer-events-none px-4">
            <?php if(isset($_GET['info']) && $_GET['info'] == 'registro_requerido'): ?>
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-800 p-4 rounded-lg shadow-lg pointer-events-auto w-full max-w-2xl animate-fade-in">
                    <p class="font-bold"><i class="fa-solid fa-circle-info mr-2"></i> ¡Hola! Debes registrarte primero</p>
                    <p class="text-sm">Para poder reservar un vehículo necesitamos tus datos legales para el contrato.</p>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success']) && $_GET['success'] == 'mensaje_enviado'): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-lg pointer-events-auto w-full max-w-2xl animate-fade-in">
                    <p class="font-bold"><i class="fa-solid fa-paper-plane mr-2"></i> ¡Mensaje Enviado!</p>
                    <p class="text-sm">Hemos recibido tu consulta. Nuestro equipo te contactará muy pronto.</p>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error']) && $_GET['error'] == 'perfil_incompleto'): ?>
                <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-800 p-4 rounded-lg shadow-lg pointer-events-auto w-full max-w-2xl animate-fade-in">
                    <p class="font-bold"><i class="fa-solid fa-id-card mr-2"></i> Perfil Incompleto</p>
                    <p class="text-sm">Tu cuenta no tiene una cédula ni licencia asociada. Por favor, crea una cuenta nueva con todos tus datos.</p>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success']) && $_GET['success'] == 'reserva_completada'): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-lg pointer-events-auto w-full max-w-2xl animate-fade-in">
                    <p class="font-bold"><i class="fa-solid fa-circle-check mr-2"></i> ¡Reserva Exitosa!</p>
                    <p class="text-sm">Tu vehículo ha sido reservado. Puedes ver los detalles en tu perfil.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-gradient-to-br from-blue-900 to-blue-700 text-white py-20 lg:py-32 overflow-hidden relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center md:text-left flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 mb-10 md:mb-0 animate-fade-in">
                    <span class="uppercase tracking-widest text-blue-200 text-sm font-semibold mb-4 block">Isla de Margarita, Venezuela</span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Explora la isla con <br> <span class="text-blue-200">libertad y estilo.</span>
                    </h1>
                    <p class="text-lg text-blue-100 mb-8 max-w-xl mx-auto md:mx-0">
                        La agencia de alquiler de vehículos líder en Nueva Esparta. Flota moderna, tarifas transparentes y atención personalizada 24/7.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <a href="#flota" class="bg-white text-blue-900 font-bold px-8 py-4 rounded-lg shadow-lg hover:bg-gray-50 transition-transform transform hover:-translate-y-1 text-center">
                            Ver Vehículos
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2 flex justify-center animate-fade-in" style="animation-delay: 0.2s;">
                  <div class="w-full max-w-md aspect-video bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 flex items-center justify-center p-8 shadow-2xl">
                    <img src="assets/img/logo.png" alt="Logo de la Empresa" class="max-h-full max-w-full object-contain">   
                  </div>
                </div>

            </div>
        </div>

        <div class="py-20 bg-white" id="flota">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900">Nuestra Flota Disponible</h2>
                    <p class="mt-4 text-gray-500">Vehículos en perfectas condiciones listos para tu aventura en Margarita.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php if ($resultado_vehiculos && $resultado_vehiculos->num_rows > 0): ?>
                        <?php while($carro = $resultado_vehiculos->fetch_assoc()): ?>
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transform transition duration-300 hover:shadow-xl hover:-translate-y-2 group">
                                <div class="relative h-48 overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($carro['Imagen_URL']); ?>" alt="<?php echo htmlspecialchars($carro['Marca'] . ' ' . $carro['Modelo']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <div class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                        Disponible
                                    </div>
                                    <div class="absolute top-4 left-4 bg-gray-900/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full">
                                        <i class="fa-solid fa-user-group mr-1"></i> <?php echo htmlspecialchars($carro['Capacidad']); ?>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($carro['Marca'] . ' ' . $carro['Modelo']); ?></h3>
                                            <p class="text-sm text-gray-500">Año <?php echo htmlspecialchars($carro['Anio']); ?> • <?php echo htmlspecialchars($carro['Color']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-2xl font-bold text-blue-900">
                                                $<?php echo htmlspecialchars($carro['Precio_Dia'] ?? 'N/A'); ?>
                                            </span>
                                            <span class="text-xs text-gray-500 block">/día</span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!$is_logged_in): ?>
                                        <a href="index.php?info=registro_requerido" class="block w-full text-center py-3 bg-blue-50 text-blue-900 font-bold rounded-lg hover:bg-blue-900 hover:text-white transition-colors">
                                            Reservar Ahora
                                        </a>
                                    <?php elseif ($role === 'Cliente' && !$is_valid_client): ?>
                                        <a href="index.php?error=perfil_incompleto" class="block w-full text-center py-3 bg-orange-100 text-orange-800 font-bold rounded-lg hover:bg-orange-600 hover:text-white transition-colors">
                                            <i class="fa-solid fa-id-card mr-1"></i> Completar Registro
                                        </a>
                                    <?php elseif ($role === 'Admin'): ?>
                                        <button disabled class="block w-full text-center py-3 bg-gray-100 text-gray-400 font-bold rounded-lg cursor-not-allowed">
                                            Solo Clientes
                                        </button>
                                    <?php else: ?>
                                        <a href="views/reservas/checkout.php?id=<?php echo $carro['ID_Vehiculo']; ?>" class="block w-full text-center py-3 bg-blue-50 text-blue-900 font-bold rounded-lg hover:bg-blue-900 hover:text-white transition-colors">
                                            Reservar Ahora
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-3 text-center py-10">
                            <i class="fa-solid fa-car-burst text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Lo sentimos, en este momento todos nuestros vehículos están alquilados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="py-20 bg-gray-50" id="servicios">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900">¿Por qué elegir Mundo Rental?</h2>
                    <p class="mt-4 text-gray-500">Garantizamos una experiencia de alquiler sin complicaciones.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center transform transition duration-300 hover:shadow-md hover:-translate-y-2">
                        <div class="w-16 h-16 bg-blue-50 text-blue-900 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Tarifas Transparentes</h3>
                        <p class="text-gray-500">Sin costos ocultos. Cobro justo por días completos y horas adicionales calculadas con precisión.</p>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center transform transition duration-300 hover:shadow-md hover:-translate-y-2">
                        <div class="w-16 h-16 bg-blue-50 text-blue-900 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Depósito Seguro</h3>
                        <p class="text-gray-500">Tu seguridad es primero. Manejamos un depósito estándar de $100 reembolsable al entregar el vehículo.</p>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center transform transition duration-300 hover:shadow-md hover:-translate-y-2">
                        <div class="w-16 h-16 bg-blue-50 text-blue-900 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Soporte Local</h3>
                        <p class="text-gray-500">Estamos en Margarita. Si nos necesitas, nuestro equipo te asiste rápidamente en cualquier punto de la isla.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="py-20 bg-white border-t border-gray-100" id="contacto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900">Contáctanos</h2>
                    <p class="mt-4 text-gray-500">¿Tienes alguna solicitud especial o duda sobre tu reserva? Escríbenos.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 bg-gray-50 rounded-3xl overflow-hidden shadow-sm border border-gray-100">
                    
                    <div class="bg-blue-900 text-white p-10 md:p-12 flex flex-col justify-center">
                        <h3 class="text-2xl font-bold mb-6">Información de la Agencia</h3>
                        <p class="text-blue-200 mb-8 leading-relaxed">Estamos ubicados estratégicamente para atenderte desde tu llegada a la isla. Operamos todos los días del año.</p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <i class="fa-solid fa-location-dot mt-1 text-xl text-blue-300"></i>
                                <div>
                                    <p class="font-bold text-white">Oficina Principal</p>
                                    <p class="text-blue-200 text-sm">Av. Aldonza Manrique, Pampatar<br>Isla de Margarita, Venezuela.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <i class="fa-solid fa-phone mt-1 text-xl text-blue-300"></i>
                                <div>
                                    <p class="font-bold text-white">Llámanos</p>
                                    <p class="text-blue-200 text-sm">+58 (0295) 262-0000<br>Atención 24/7 para emergencias</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <i class="fa-solid fa-envelope mt-1 text-xl text-blue-300"></i>
                                <div>
                                    <p class="font-bold text-white">Correo Electrónico</p>
                                    <p class="text-blue-200 text-sm">reservas@mundorental.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-10 md:p-12">
                        <form action="index.php" method="GET" class="space-y-6">
                            <input type="hidden" name="success" value="mensaje_enviado">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tu Nombre</label>
                                    <input required type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tu Correo</label>
                                    <input required type="email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Asunto</label>
                                <input required type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Mensaje</label>
                                <textarea required rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-white resize-none"></textarea>
                            </div>
                            <button type="submit" class="w-full py-4 bg-blue-900 text-white font-bold rounded-xl shadow-lg hover:bg-blue-800 transition-colors">
                                Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-300 py-12" id="contacto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-2 mb-4 md:mb-0">
                <i class="fa-solid fa-car text-xl"></i>
                <span class="font-bold text-lg text-white">MUNDO RENTAL</span>
            </div>
            <p class="text-sm">© <?php echo date('Y'); ?> Mundo Rental Margarita. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>