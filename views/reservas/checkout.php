<?php
session_start();
require_once '../../config/database_connection.php';

// 1. SEGURIDAD: Solo clientes logueados pueden reservar
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?reserva=pendiente");
    exit();
}

// 2. VALIDACIÓN: Asegurarnos de que viene un ID de vehículo en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../../index.php");
    exit();
}

$id_vehiculo = (int)$_GET['id'];

// 3. CONSULTA: Traer los datos del vehículo Y su tarifa vigente para hoy
$query = "
    SELECT v.*, 
           (SELECT Monto_Diario FROM tarifas t 
            WHERE t.ID_Vehiculo = v.ID_Vehiculo 
            AND CURDATE() BETWEEN t.Fecha_Inicio AND t.Fecha_Fin 
            LIMIT 1) as Precio_Dia
    FROM vehiculos v 
    WHERE v.ID_Vehiculo = ? AND v.Estado = 'Disponible'
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $id_vehiculo);
$stmt->execute();
$vehiculo = $stmt->get_result()->fetch_assoc();

if (!$vehiculo || empty($vehiculo['Precio_Dia'])) {
    header("Location: ../../index.php?error=nodisponible");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Completar Reserva | Mundo Rental</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <header class="bg-white shadow-sm h-20 flex items-center px-8 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto w-full flex justify-between items-center">
            <a href="../../index.php" class="flex items-center gap-2 text-blue-900 hover:text-blue-700 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Volver al Catálogo
            </a>
            <div class="font-bold text-lg tracking-widest uppercase text-gray-800">Checkout Seguro</div>
        </div>
    </header>

    <main class="flex-grow py-12 px-4">
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="md:col-span-2">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fa-regular fa-calendar-check text-blue-900 mr-2"></i> Detalles de tu Viaje</h2>
                    
                    <form action="../../controllers/reserva_controller.php" method="POST" id="form-reserva">
                        <input type="hidden" name="id_vehiculo" value="<?php echo $vehiculo['ID_Vehiculo']; ?>">
                        <input type="hidden" id="precio_base" value="<?php echo $vehiculo['Precio_Dia']; ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Fecha y Hora de Retiro</label>
                                <input type="datetime-local" id="fecha_salida" name="fecha_salida" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-gray-50 text-gray-700">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Fecha y Hora de Devolución</label>
                                <input type="datetime-local" id="fecha_devolucion" name="fecha_devolucion" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-gray-50 text-gray-700">
                            </div>
                        </div>

                        <div class="bg-blue-50 text-blue-900 p-4 rounded-xl text-sm flex gap-3 mb-8">
                            <i class="fa-solid fa-circle-info mt-0.5"></i>
                            <p>Recuerda que debes presentar tu Cédula o Pasaporte y tu Licencia de Conducir vigente al momento de retirar el vehículo en nuestras oficinas.</p>
                        </div>

                        <button type="submit" id="btn-pagar" class="w-full py-4 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-xl shadow-lg transition-transform transform hover:-translate-y-1 text-lg flex items-center justify-center gap-2 opacity-50 cursor-not-allowed" disabled>
                            <i class="fa-solid fa-lock"></i> Confirmar y Pagar
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-28">
                    <div class="h-48 overflow-hidden bg-gray-100">
                        <img src="../../<?php echo $vehiculo['Imagen_URL']; ?>" class="w-full h-full object-cover" alt="Vehículo">
                    </div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-1"><?php echo $vehiculo['Marca'] . ' ' . $vehiculo['Modelo']; ?></h3>
                        <p class="text-sm text-gray-500 mb-6 font-mono"><?php echo $vehiculo['Placa']; ?> • <?php echo $vehiculo['Anio']; ?></p>

                        <div class="space-y-4 text-sm border-b border-gray-100 pb-6 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Tarifa Diaria</span>
                                <span class="font-bold text-gray-800">$<?php echo $vehiculo['Precio_Dia']; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-start text-gray-600">
                                <span>Tiempo de Alquiler</span>
                                <span class="font-bold text-gray-800 text-right" id="resumen-tiempo">0 día(s)</span>
                            </div>
                            
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal Alquiler</span>
                                <span class="font-bold text-gray-800" id="resumen-subtotal">$0.00</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span class="flex items-center gap-1">Depósito Reembolsable <i class="fa-solid fa-circle-question text-gray-400" title="Se te devuelve al entregar el carro intacto"></i></span>
                                <span class="font-bold text-gray-800">$100.00</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end">
                            <span class="text-gray-900 font-bold uppercase tracking-wider text-sm">Total a Pagar</span>
                            <span class="text-3xl font-bold text-blue-900" id="resumen-total">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        const inputSalida = document.getElementById('fecha_salida');
        const inputDevolucion = document.getElementById('fecha_devolucion');
        const precioBase = parseFloat(document.getElementById('precio_base').value);
        const btnPagar = document.getElementById('btn-pagar');

        function calcularTotal() {
            const salida = new Date(inputSalida.value);
            const devolucion = new Date(inputDevolucion.value);

            if (inputSalida.value && inputDevolucion.value && devolucion > salida) {
                
                const diferenciaTiempo = Math.abs(devolucion - salida);
                const horasTotales = Math.round(diferenciaTiempo / (1000 * 60 * 60));
                
                let diasCobrados = Math.floor(horasTotales / 24);
                let horasExtra = horasTotales % 24;
                
                // Mínimo de cobro: 1 día si el alquiler es por menos de 24h
                if (diasCobrados === 0 && horasExtra === 0) {
                    diasCobrados = 1;
                } else if (diasCobrados === 0 && horasExtra > 0) {
                    diasCobrados = 1;
                    horasExtra = 0;
                }

                const subtotalDias = diasCobrados * precioBase;
                const subtotalHoras = horasExtra * 10.00; 
                const subtotal = subtotalDias + subtotalHoras;
                const deposito = 100.00;
                const totalFinal = subtotal + deposito;

                // TEXTO CON EL DESGLOSE DE HORAS EXTRA
                let textoTiempo = `${diasCobrados} día(s)`;
                if (horasExtra > 0) {
                    // Usamos HTML para poner el textito gris abajo
                    textoTiempo += `<br><span class="text-xs text-gray-400 font-normal mt-1 block">+ ${horasExtra} hora(s) extra a $10.00 c/u</span>`;
                }
                
                // Cambiamos a innerHTML para que lea las etiquetas <br> y <span>
                document.getElementById('resumen-tiempo').innerHTML = textoTiempo;
                document.getElementById('resumen-subtotal').innerText = '$' + subtotal.toFixed(2);
                document.getElementById('resumen-total').innerText = '$' + totalFinal.toFixed(2);

                btnPagar.disabled = false;
                btnPagar.classList.remove('opacity-50', 'cursor-not-allowed');

            } else {
                document.getElementById('resumen-tiempo').innerHTML = '0 día(s)';
                document.getElementById('resumen-subtotal').innerText = '$0.00';
                document.getElementById('resumen-total').innerText = '$0.00';
                btnPagar.disabled = true;
                btnPagar.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        inputSalida.addEventListener('change', calcularTotal);
        inputDevolucion.addEventListener('change', calcularTotal);

        const hoy = new Date().toISOString().slice(0, 16);
        inputSalida.min = hoy;
        inputDevolucion.min = hoy;
    </script>
</body>
</html>