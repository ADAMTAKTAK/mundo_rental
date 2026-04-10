<?php
session_start();
require_once '../../config/database_connection.php';

// 1. Validar Sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?reserva=pendiente");
    exit();
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../../index.php");
    exit();
}

$id_vehiculo = (int)$_GET['id'];

// 2. Traer el vehículo y su precio vigente hoy
$query = "
    SELECT v.*, 
           (SELECT Monto_Diario FROM tarifas t WHERE t.ID_Vehiculo = v.ID_Vehiculo AND CURDATE() BETWEEN t.Fecha_Inicio AND t.Fecha_Fin LIMIT 1) as Precio_Dia
    FROM vehiculos v WHERE v.ID_Vehiculo = ? AND v.Estado = 'Disponible'
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $id_vehiculo);
$stmt->execute();
$vehiculo = $stmt->get_result()->fetch_assoc();

if (!$vehiculo || empty($vehiculo['Precio_Dia'])) {
    header("Location: ../../index.php?error=nodisponible");
    exit();
}

// 3. Traer Catálogo de Extras
$servicios = $connection->query("SELECT * FROM servicios ORDER BY Nombre");
$accesorios = $connection->query("SELECT * FROM accesorios WHERE Stock_Total > 0 ORDER BY Nombre");
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
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow-sm">
                        <p class="font-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Error al procesar</p>
                        <p class="text-sm">Hubo un problema al guardar tu reserva en el sistema. Intenta nuevamente o contacta a soporte.</p>
                    </div>
                <?php endif; ?>

                <form action="../../controllers/reserva_controller.php" method="POST" id="form-reserva">
                    <input type="hidden" name="id_vehiculo" value="<?php echo $vehiculo['ID_Vehiculo']; ?>">
                    <input type="hidden" id="precio_base" value="<?php echo $vehiculo['Precio_Dia']; ?>">

                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fa-regular fa-calendar-check text-blue-900 mr-2"></i> Fechas de Viaje</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Retiro</label>
                                <input type="datetime-local" id="fecha_salida" name="fecha_salida" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Devolución</label>
                                <input type="datetime-local" id="fecha_devolucion" name="fecha_devolucion" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fa-solid fa-plus-minus text-blue-900 mr-2"></i> Personaliza tu Alquiler</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="font-bold text-gray-700 border-b pb-2 mb-3 text-sm uppercase">Accesorios (Por día)</h3>
                                <div class="space-y-3">
                                    <?php while($a = $accesorios->fetch_assoc()): ?>
                                    <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="accesorios[]" value="<?php echo $a['ID_Accesorio']; ?>" data-precio="<?php echo $a['Precio_Diario']; ?>" class="extra-cb w-5 h-5 text-blue-900 rounded">
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($a['Nombre']); ?></p>
                                            <p class="text-xs text-blue-600">+$<?php echo $a['Precio_Diario']; ?> / día</p>
                                        </div>
                                    </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-700 border-b pb-2 mb-3 text-sm uppercase">Servicios (Fijo)</h3>
                                <div class="space-y-3">
                                    <?php while($s = $servicios->fetch_assoc()): ?>
                                    <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="servicios[]" value="<?php echo $s['ID_Servicio']; ?>" data-precio="<?php echo $s['Precio_Base']; ?>" class="extra-cb w-5 h-5 text-blue-900 rounded" data-tipo="fijo">
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($s['Nombre']); ?></p>
                                            <p class="text-xs text-blue-600">+$<?php echo $s['Precio_Base']; ?> (Único)</p>
                                        </div>
                                    </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="btn-pagar" class="w-full py-4 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-xl shadow-lg transition-transform transform hover:-translate-y-1 text-lg flex items-center justify-center gap-2 opacity-50 cursor-not-allowed" disabled>
                        <i class="fa-solid fa-lock"></i> Confirmar y Pagar
                    </button>
                </form>
            </div>

            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-28">
                    <div class="h-48 overflow-hidden bg-gray-100">
                        <img src="../../<?php echo $vehiculo['Imagen_URL']; ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-1"><?php echo $vehiculo['Marca'] . ' ' . $vehiculo['Modelo']; ?></h3>
                        <p class="text-sm text-gray-500 mb-6 font-mono"><?php echo $vehiculo['Placa']; ?> • <?php echo $vehiculo['Anio']; ?></p>

                        <div class="space-y-4 text-sm border-b border-gray-100 pb-6 mb-6">
                            <div class="flex justify-between text-gray-600"><span>Tarifa Diaria</span><span class="font-bold text-gray-800">$<?php echo $vehiculo['Precio_Dia']; ?></span></div>
                            <div class="flex justify-between items-start text-gray-600"><span>Tiempo Alquiler</span><span class="font-bold text-gray-800 text-right" id="resumen-tiempo">0 día(s)</span></div>
                            <div class="flex justify-between text-gray-600 hidden" id="fila-adicionales"><span>Adicionales</span><span class="font-bold text-gray-800" id="resumen-adicionales">$0.00</span></div>
                            <div class="flex justify-between text-gray-600"><span>Subtotal Alquiler</span><span class="font-bold text-gray-800" id="resumen-subtotal">$0.00</span></div>
                            <div class="flex justify-between text-gray-600"><span>Depósito Reembolsable</span><span class="font-bold text-gray-800">$100.00</span></div>
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
        const checkboxes = document.querySelectorAll('.extra-cb');
        const btnPagar = document.getElementById('btn-pagar');

        function calcular() {
            const salida = new Date(inputSalida.value);
            const devolucion = new Date(inputDevolucion.value);

            if (inputSalida.value && inputDevolucion.value && devolucion > salida) {
                const diff = Math.abs(devolucion - salida);
                const horasTotales = Math.round(diff / (1000 * 60 * 60));
                
                let dias = Math.floor(horasTotales / 24);
                let horasExtra = horasTotales % 24;
                
                if (dias === 0) { dias = 1; horasExtra = 0; }

                let costoHoras = horasExtra * 10.00;
                if (costoHoras >= precioBase) {
                    dias += 1;
                    horasExtra = 0;
                    costoHoras = 0;
                }

                let totalAdicionales = 0;
                checkboxes.forEach(cb => {
                    if(cb.checked) {
                        const p = parseFloat(cb.dataset.precio);
                        totalAdicionales += (cb.dataset.tipo === 'fijo') ? p : (p * dias);
                    }
                });

                const subtotalVehiculo = (dias * precioBase) + costoHoras;
                const subtotalFinal = subtotalVehiculo + totalAdicionales;
                const totalConDeposito = subtotalFinal + 100.00;

                let txt = `${dias} día(s)`;
                if (horasExtra > 0) txt += `<br><span class="text-xs text-gray-400">+ ${horasExtra} h extra ($10/u)</span>`;
                
                document.getElementById('resumen-tiempo').innerHTML = txt;
                document.getElementById('resumen-adicionales').innerText = '$' + totalAdicionales.toFixed(2);
                document.getElementById('fila-adicionales').classList.toggle('hidden', totalAdicionales === 0);
                document.getElementById('resumen-subtotal').innerText = '$' + subtotalFinal.toFixed(2);
                document.getElementById('resumen-total').innerText = '$' + totalConDeposito.toFixed(2);

                btnPagar.disabled = false;
                btnPagar.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        inputSalida.addEventListener('change', calcular);
        inputDevolucion.addEventListener('change', calcular);
        checkboxes.forEach(cb => cb.addEventListener('change', calcular));
        
        const hoy = new Date().toISOString().slice(0, 16);
        inputSalida.min = hoy; inputDevolucion.min = hoy;
    </script>
</body>
</html>