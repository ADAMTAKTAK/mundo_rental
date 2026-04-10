<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $tipo_doc = $_POST['tipo_doc'];
    $num_doc = trim($_POST['num_doc']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $licencia = trim($_POST['licencia']);
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $connection->begin_transaction();

    try {
        $stmt_c = $connection->prepare("INSERT INTO clientes (Tipo_Documento, Numero_Documento, Nombre, Apellido, Telefono, Licencia_Conducir, Email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_c->bind_param("sssssss", $tipo_doc, $num_doc, $nombre, $apellido, $telefono, $licencia, $email);
        $stmt_c->execute();
        
        $id_cliente = $connection->insert_id;

        if (!empty($username) && !empty($password)) {
            $rol = 'Cliente';
            $stmt_u = $connection->prepare("INSERT INTO usuarios (Username, Password, Nombre, Apellido, Email, Rol, ID_Cliente) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_u->bind_param("ssssssi", $username, $password, $nombre, $apellido, $email, $rol, $id_cliente);
            $stmt_u->execute();
        }

        $connection->commit();
        header("Location: ../views/admin/clientes.php?success=creado");

    } catch (mysqli_sql_exception $e) {
        $connection->rollback();
        header("Location: ../views/admin/nuevo_cliente.php?error=duplicado");
    }
    exit();
}