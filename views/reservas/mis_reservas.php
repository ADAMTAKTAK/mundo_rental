<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Cliente') {
    header("Location: ../auth/login.php");
    exit();
}

$stmt_user = $connection->prepare("SELECT ID_Cliente FROM usuarios WHERE ID_Usuario = ?");
$stmt_user->bind_param("i", $_SESSION['user_id']);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

if (empty($user_data['ID_Cliente'])) {
    header("Location: ../../index.php?error=perfil_incompleto");
    exit();
}
$id_cliente = $user_data['ID_Cliente'];

$query = "
    SELECT a.ID_Alquiler, a.Fecha_Salida, a.Fecha_Devolucion_Prevista, a.Monto_Total, a.Estado, a.Deposito_Garantia,
           v.Marca, v.Modelo, v.Placa, v.Imagen_URL,
           FN_Calcular_Extras_Contrato(a.ID_Alquiler) AS Costo_Extras
    FROM alquileres a
    JOIN vehiculos v ON a.ID_Vehiculo = v.ID_Vehiculo
    WHERE a.ID_Cliente = ?
    ORDER BY a.Fecha_Salida DESC
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$reservas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas | Mundo Rental</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <header class="bg-white shadow-sm h-20 flex items-center px-8 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto w-full flex justify-between items-center">
            <a href="../../index.php" class="flex items-center gap-2 text-blue-900 font-bold hover:text-blue-700 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
            </a>
            <div class="font-bold text-lg tracking-widest uppercase text-gray-800">Mi Historial</div>
        </div>
    </header>

    <main class="flex-grow py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-black text-gray-900 mb-8 uppercase tracking-tighter">Mis Reservas</h1>

            <?php if($reservas->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while($r = $reservas->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row gap-6 items-center">
                        <div class="w-full md:w-48 h-32 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                            <img src="../../<?php echo $r['Imagen_URL']; ?>" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-grow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900"><?php echo $r['Marca'] . ' ' . $r['Modelo']; ?></h3>
                                    <p class="text-sm text-gray-500 font-mono mb-2">Placa: <?php echo $r['Placa']; ?></p>
                                </div>
                                <?php 
                                    $bg = 'bg-gray-100 text-gray-600';
                                    if($r['Estado'] == 'Reservado') $bg = 'bg-blue-100 text-blue-700';
                                    if($r['Estado'] == 'En Curso') $bg = 'bg-orange-100 text-orange-700';
                                    if($r['Estado'] == 'Finalizado') $bg = 'bg-green-100 text-green-700';
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $bg; ?>">
                                    <?php echo $r['Estado']; ?>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm mt-4">
                                <div>
                                    <p class="text-gray-400 text-xs font-bold uppercase">Retiro</p>
                                    <p class="text-gray-800 font-medium"><?php echo date('d/m/Y H:i', strtotime($r['Fecha_Salida'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs font-bold uppercase">Devolución</p>
                                    <p class="text-gray-800 font-medium"><?php echo date('d/m/Y H:i', strtotime($r['Fecha_Devolucion_Prevista'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-48 border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6 text-right">
                            <p class="text-xs text-gray-400 font-bold uppercase mb-1">Monto Total</p>
                            <p class="text-2xl font-black text-blue-900 mb-1">$<?php echo number_format($r['Monto_Total'], 2); ?></p>
                            <?php if($r['Costo_Extras'] > 0): ?>
                                <p class="text-[10px] text-gray-500 mb-3">Incluye $<?php echo number_format($r['Costo_Extras'], 2); ?> en extras</p>
                            <?php else: ?>
                                <div class="mb-3"></div>
                            <?php endif; ?>
                            
                            <a href="recibo.php?id=<?php echo $r['ID_Alquiler']; ?>" class="inline-block w-full text-center px-4 py-2 bg-blue-50 text-blue-900 hover:bg-blue-900 hover:text-white font-bold rounded-lg transition-colors text-sm">
                                Ver Recibo PDF
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <i class="fa-solid fa-car-side text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Aún no tienes reservas</h3>
                    <p class="text-gray-500 mb-6">Explora nuestra flota y comienza tu aventura en la isla.</p>
                    <a href="../../index.php#flota" class="inline-block px-6 py-3 bg-blue-900 text-white font-bold rounded-xl shadow-lg hover:bg-blue-800 transition-colors">
                        Ver Vehículos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>