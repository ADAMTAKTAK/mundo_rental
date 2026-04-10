<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    $id_vehiculo = $_POST['id_vehiculo'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $placa = strtoupper($_POST['placa']);
    $anio = $_POST['anio'];
    $color = $_POST['color'];
    $capacidad = $_POST['capacidad'];
    $id_categoria = $_POST['id_categoria'];
    $estado = $_POST['estado'];

    $stmt_img = $connection->prepare("SELECT Imagen_URL FROM vehiculos WHERE ID_Vehiculo = ?");
    $stmt_img->bind_param("i", $id_vehiculo);
    $stmt_img->execute();
    $ruta_db = $stmt_img->get_result()->fetch_assoc()['Imagen_URL'];

    if (isset($_FILES['foto_carro']) && $_FILES['foto_carro']['error'] === UPLOAD_ERR_OK) {
        $carpeta_destino = '../assets/img/vehiculos/';
        $nombre_original = basename($_FILES['foto_carro']['name']);
        $nombre_unico = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $nombre_original);
        $ruta_fisica = $carpeta_destino . $nombre_unico;

        if (move_uploaded_file($_FILES['foto_carro']['tmp_name'], $ruta_fisica)) {
            $ruta_db = 'assets/img/vehiculos/' . $nombre_unico;
        }
    }

    $query = "UPDATE vehiculos SET ID_Categoria=?, Placa=?, Marca=?, Modelo=?, Anio=?, Color=?, Capacidad=?, Estado=?, Imagen_URL=? WHERE ID_Vehiculo=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("isssisissi", $id_categoria, $placa, $marca, $modelo, $anio, $color, $capacidad, $estado, $ruta_db, $id_vehiculo);

    if ($stmt->execute()) {
        header("Location: ../views/admin/vehiculos.php?success=editado");
    } else {
        header("Location: ../views/admin/editar_vehiculo.php?id=$id_vehiculo&error=db");
    }
    exit();
}