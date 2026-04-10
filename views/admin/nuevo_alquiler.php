<?php
session_start();
require_once '../../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}

$clientes = $connection->query("SELECT ID_Cliente, Nombre, Apellido, Numero_Documento FROM clientes ORDER BY Nombre ASC");
$vehiculos = $connection->query("
    SELECT v.*, 
           (SELECT Monto_Diario FROM tarifas t WHERE t.ID_Vehiculo = v.ID_Vehiculo AND CURDATE() BETWEEN t.Fecha_Inicio AND t.Fecha_Fin LIMIT 1) as Precio_Dia
    FROM vehiculos v WHERE v.Estado = 'Disponible'
");
$servicios = $connection->query("SELECT * FROM servicios ORDER BY Nombre");
$accesorios = $connection->query("SELECT * FROM accesorios WHERE Stock_Total > 0 ORDER BY Nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Alquiler Presencial | Admin</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="max-w-6xl mx-auto py-10 px-4">
        
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-black text-blue-900 uppercase tracking-tighter">
                <i class="fa-solid fa-file-signature mr-2"></i> Nuevo Contrato Físico
            </h1>
            <a href="alquileres.php" class="text-gray-500 hover:text-gray-800 font-bold transition-colors">
                <i class="fa-solid fa-xmark mr-1"></i> Cancelar
            </a>
        </div>

        <form action="../../controllers/nuevo_alquiler_controller.php" method="POST" id="form-admin-alquiler" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                    <h2 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-6 border-b pb-2">1. Información del Cliente</h2>
                    <div class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Buscar Cliente Registrado</label>
                            <select name="id_cliente" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-gray-50">
                                <option value="">-- Seleccionar Cliente --</option>
                                <?php while($c = $clientes->fetch_assoc()): ?>
                                    <option value="<?php echo $c['ID_Cliente']; ?>">
                                        <?php echo $c['Nombre'].' '.$c['Apellido'].' ('.$c['Numero_Documento'].')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <a href="clientes.php" class="bg-blue-50 text-blue-900 p-3.5 rounded-xl hover:bg-blue-100 transition-colors" title="Crear Cliente Nuevo">
                            <i class="fa-solid fa-user-plus"></i>
                        </a>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                    <h2 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-6 border-b pb-2">2. Vehículo y Período</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Vehículo Disponible</label>
                            <select name="id_vehiculo" id="id_vehiculo" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-900 outline-none bg-gray-50">
                                <option value="" data-precio="0">-- Seleccionar Vehículo --</option>
                                <?php while($v = $vehiculos->fetch_assoc()): ?>
                                    <option value="<?php echo $v['ID_Vehiculo']; ?>" data-precio="<?php echo $v['Precio_Dia']; ?>">
                                        <?php echo $v['Marca'].' '.$v['Modelo'].' ('.$v['Placa'].') - $'.$v['Precio_Dia'].'/día'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Fecha/Hora Entrega</label>
                            <input type="datetime-local" name="fecha_salida" id="fecha_salida" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Fecha/Hora Devolución</label>
                            <input type="datetime-local" name="fecha_devolucion" id="fecha_devolucion" required class="w-full px-4 py-3 border border-gray-200 rounded-xl outline-none bg-gray-50">
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                    <h2 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-6 border-b pb-2">3. Adicionales del Contrato</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-bold text-gray-700 text-sm mb-4 uppercase">Accesorios (Por día)</h3>
                            <div class="space-y-3">
                                <?php while($a = $accesorios->fetch_assoc()): ?>
                                <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox" name="accesorios[]" value="<?php echo $a['ID_Accesorio']; ?>" data-precio="<?php echo $a['Precio_Diario']; ?>" class="extra-cb w-5 h-5 text-blue-900 rounded focus:ring-blue-900">
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($a['Nombre']); ?></p>
                                        <p class="text-xs text-blue-600">+$<?php echo $a['Precio_Diario']; ?> / día</p>
                                    </div>
                                </label>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-700 text-sm mb-4 uppercase">Servicios (Monto Fijo)</h3>
                            <div class="space-y-3">
                                <?php while($s = $servicios->fetch_assoc()): ?>
                                <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox" name="servicios[]" value="<?php echo $s['ID_Servicio']; ?>" data-precio="<?php echo $s['Precio_Base']; ?>" class="extra-cb w-5 h-5 text-blue-900 rounded focus:ring-blue-900" data-tipo="fijo">
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
            </div>

            <div class="lg:col-span-1">
                <div class="bg-blue-900 text-white rounded-3xl p-8 shadow-xl sticky top-10">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-300 mb-6">Resumen de Cobro</h3>
                    
                    <div class="space-y-4 border-b border-blue-800 pb-6 mb-6">
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-blue-200">Tiempo de Alquiler:</span>
                            <span class="font-bold text-right" id="resumen-dias">0 día(s)</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-200">Subtotal Carro:</span>
                            <span class="font-bold" id="resumen-sub-carro">$0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-200">Extras (Serv/Acc):</span>
                            <span class="font-bold" id="resumen-extras">$0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-blue-200">Depósito Garantía:</span>
                            <span class="font-bold">$100.00</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-end mb-8">
                        <span class="text-xs font-bold uppercase tracking-widest text-blue-300">Total a Cobrar</span>
                        <span class="text-4xl font-black" id="resumen-total">$0.00</span>
                    </div>

                    <button type="submit" class="w-full py-4 bg-white text-blue-900 font-black rounded-2xl shadow-lg hover:bg-blue-50 transition-transform active:scale-95 text-lg uppercase tracking-tighter">
                        Crear Contrato
                    </button>
                    <p class="text-[10px] text-blue-400 mt-4 text-center leading-tight">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Si la fecha de entrega es HOY, el contrato se marcará "En Curso". Si es a futuro, se marcará como "Reservado".
                    </p>
                </div>
            </div>
        </form>
    </div>

    <script>
        const selVehiculo = document.getElementById('id_vehiculo');
        const inSalida = document.getElementById('fecha_salida');
        const inDevolucion = document.getElementById('fecha_devolucion');
        const checkboxes = document.querySelectorAll('.extra-cb');

        function recalcular() {
            const precioDia = parseFloat(selVehiculo.options[selVehiculo.selectedIndex].dataset.precio || 0);
            const d1 = new Date(inSalida.value);
            const d2 = new Date(inDevolucion.value);

            if (inSalida.value && inDevolucion.value && d2 > d1) {
                const diff = Math.abs(d2 - d1);
                const horasTotales = Math.round(diff / (1000 * 60 * 60));
                
                let dias = Math.floor(horasTotales / 24);
                let horasExtra = horasTotales % 24;
                if (dias === 0) { dias = 1; horasExtra = 0; }

                let costoHoras = horasExtra * 10.00;
                if (costoHoras >= precioDia) { dias += 1; horasExtra = 0; costoHoras = 0; }

                let totalExtras = 0;
                checkboxes.forEach(cb => {
                    if(cb.checked) {
                        const p = parseFloat(cb.dataset.precio);
                        totalExtras += (cb.dataset.tipo === 'fijo') ? p : (p * dias);
                    }
                });

                const subVehiculo = (dias * precioDia) + costoHoras;
                const totalFinal = subVehiculo + totalExtras + 100.00;

                let txtTiempo = `${dias} día(s)`;
                if (horasExtra > 0) {
                    txtTiempo += `<br><span class="text-[11px] text-blue-300 font-normal mt-1 block">+ ${horasExtra} h extra a $10.00 c/u</span>`;
                }

                document.getElementById('resumen-dias').innerHTML = txtTiempo;
                document.getElementById('resumen-sub-carro').innerText = '$' + subVehiculo.toFixed(2);
                document.getElementById('resumen-extras').innerText = '$' + totalExtras.toFixed(2);
                document.getElementById('resumen-total').innerText = '$' + totalFinal.toFixed(2);
            }
        }

        selVehiculo.addEventListener('change', recalcular);
        inSalida.addEventListener('change', recalcular);
        inDevolucion.addEventListener('change', recalcular);
        checkboxes.forEach(cb => cb.addEventListener('change', recalcular));

        const ahora = new Date().toISOString().slice(0, 16);
        inSalida.value = ahora;
    </script>
</body>
</html>