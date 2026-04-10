<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $placa = strtoupper($_POST['placa']);
    $anio = $_POST['anio'];
    $color = $_POST['color'];
    $capacidad = $_POST['capacidad'];
    $id_categoria = $_POST['id_categoria'];
    
    $estado_inicial = 'Disponible';
    $ruta_db = 'assets/img/default.jpg';

    if (isset($_FILES['foto_carro']) && $_FILES['foto_carro']['error'] === UPLOAD_ERR_OK) {
        
        $carpeta_destino = '../assets/img/vehiculos/';
        $nombre_original = basename($_FILES['foto_carro']['name']);
        
        $nombre_unico = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $nombre_original);
        $ruta_fisica = $carpeta_destino . $nombre_unico;
        
        if (move_uploaded_file($_FILES['foto_carro']['tmp_name'], $ruta_fisica)) {
            $ruta_db = 'assets/img/vehiculos/' . $nombre_unico;
        }
    }

    try {
        $stmt = $connection->prepare("INSERT INTO vehiculos (ID_Categoria, Placa, Marca, Modelo, Anio, Color, Capacidad, Estado, Imagen_URL) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisiss", $id_categoria, $placa, $marca, $modelo, $anio, $color, $capacidad, $estado_inicial, $ruta_db);
        
        if ($stmt->execute()) {
            header("Location: ../views/admin/dashboard.php?success=vehiculo_creado");
        } else {
            header("Location: ../views/admin/nuevo_vehiculo.php?error=db");
        }
    } catch (Exception $e) {
        header("Location: ../views/admin/nuevo_vehiculo.php?error=placa_duplicada");
    }
    exit();
}