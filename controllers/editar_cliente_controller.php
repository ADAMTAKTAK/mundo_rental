<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    
    $id_cliente = $_POST['id_cliente'];
    $id_usuario_existente = $_POST['id_usuario_existente'];
    
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
        // 1. Actualizar Cliente
        $stmt_c = $connection->prepare("UPDATE clientes SET Tipo_Documento=?, Numero_Documento=?, Nombre=?, Apellido=?, Telefono=?, Licencia_Conducir=?, Email=? WHERE ID_Cliente=?");
        $stmt_c->bind_param("sssssssi", $tipo_doc, $num_doc, $nombre, $apellido, $telefono, $licencia, $email, $id_cliente);
        $stmt_c->execute();

        // 2. Lógica del Usuario Web
        if (!empty($username)) {
            if (!empty($id_usuario_existente)) {
                // Actualizar usuario existente
                if (!empty($password)) {
                    $stmt_u = $connection->prepare("UPDATE usuarios SET Username=?, Password=?, Nombre=?, Apellido=?, Email=? WHERE ID_Usuario=?");
                    $stmt_u->bind_param("sssssi", $username, $password, $nombre, $apellido, $email, $id_usuario_existente);
                } else {
                    $stmt_u = $connection->prepare("UPDATE usuarios SET Username=?, Nombre=?, Apellido=?, Email=? WHERE ID_Usuario=?");
                    $stmt_u->bind_param("ssssi", $username, $nombre, $apellido, $email, $id_usuario_existente);
                }
                $stmt_u->execute();
            } else {
                // No tenía usuario web, pero el admin llenó el campo: lo creamos
                if (empty($password)) $password = $num_doc; // Clave por defecto si no le ponen
                $rol = 'Cliente';
                $stmt_u = $connection->prepare("INSERT INTO usuarios (Username, Password, Nombre, Apellido, Email, Rol, ID_Cliente) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_u->bind_param("ssssssi", $username, $password, $nombre, $apellido, $email, $rol, $id_cliente);
                $stmt_u->execute();
            }
        }

        $connection->commit();
        header("Location: ../views/admin/clientes.php?success=actualizado");

    } catch (Exception $e) {
        $connection->rollback();
        header("Location: ../views/admin/clientes.php?error=db");
    }
    exit();
}