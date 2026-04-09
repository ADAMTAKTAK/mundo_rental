<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    
    // 1. Recibir los datos de texto
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $placa = strtoupper($_POST['placa']); // Forzamos mayúsculas
    $anio = $_POST['anio'];
    $color = $_POST['color'];
    $capacidad = $_POST['capacidad'];
    $id_categoria = $_POST['id_categoria'];
    
    $estado_inicial = 'Disponible'; // Por defecto
    $ruta_db = 'assets/img/default.jpg'; // Imagen por defecto por si algo falla

    // 2. Procesamiento de la Imagen
    if (isset($_FILES['foto_carro']) && $_FILES['foto_carro']['error'] === UPLOAD_ERR_OK) {
        
        $carpeta_destino = '../assets/img/vehiculos/';
        $nombre_original = basename($_FILES['foto_carro']['name']);
        
        // Generamos un nombre único (ej: 167890123_toyota.jpg) para evitar sobreescribir fotos
        $nombre_unico = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $nombre_original);
        $ruta_fisica = $carpeta_destino . $nombre_unico;
        
        // Movemos el archivo
        if (move_uploaded_file($_FILES['foto_carro']['tmp_name'], $ruta_fisica)) {
            // Si se movió con éxito, esta es la ruta que guardaremos en la BD
            $ruta_db = 'assets/img/vehiculos/' . $nombre_unico;
        }
    }

    // 3. Guardar en la Base de Datos
    try {
        $stmt = $connection->prepare("INSERT INTO vehiculos (ID_Categoria, Placa, Marca, Modelo, Anio, Color, Capacidad, Estado, Imagen_URL) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisiss", $id_categoria, $placa, $marca, $modelo, $anio, $color, $capacidad, $estado_inicial, $ruta_db);
        
        if ($stmt->execute()) {
            // Éxito: volvemos al dashboard
            header("Location: ../views/admin/dashboard.php?success=vehiculo_creado");
        } else {
            // Error al insertar
            header("Location: ../views/admin/nuevo_vehiculo.php?error=db");
        }
    } catch (Exception $e) {
        // Error de duplicidad (ej. Placa ya existe)
        header("Location: ../views/admin/nuevo_vehiculo.php?error=placa_duplicada");
    }
    exit();
}