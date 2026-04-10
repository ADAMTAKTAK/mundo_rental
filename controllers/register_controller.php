<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    
    $tipo_doc = $_POST['tipo_doc'];
    $num_doc = trim($_POST['num_doc']);
    $telefono = trim($_POST['telefono']);
    $licencia = trim($_POST['licencia']);
    $rol_defecto = 'Cliente';

    $connection->begin_transaction();

    try {
        $stmt_cliente = $connection->prepare("INSERT INTO clientes (Tipo_Documento, Numero_Documento, Nombre, Apellido, Telefono, Licencia_Conducir, Email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_cliente->bind_param("sssssss", $tipo_doc, $num_doc, $nombre, $apellido, $telefono, $licencia, $email);
        $stmt_cliente->execute();
        
        $id_cliente_nuevo = $connection->insert_id;

        $stmt_usuario = $connection->prepare("INSERT INTO usuarios (Username, Password, Nombre, Apellido, Email, Rol, ID_Cliente) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_usuario->bind_param("ssssssi", $username, $password, $nombre, $apellido, $email, $rol_defecto, $id_cliente_nuevo);
        $stmt_usuario->execute();

        $id_usuario_nuevo = $stmt_usuario->insert_id;

        $connection->commit();

        $_SESSION['user_id'] = $id_usuario_nuevo; 
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $rol_defecto;

        header("Location: ../index.php?success=registrado");
        exit();
        
    } catch (mysqli_sql_exception $e) {
        $connection->rollback();
        
        if (strpos($e->getMessage(), 'Username') !== false || strpos($e->getMessage(), 'Email') !== false) {
            header("Location: ../views/auth/register.php?error=usuario_existe");
        } else if (strpos($e->getMessage(), 'Tipo_Documento') !== false) {
            header("Location: ../views/auth/register.php?error=documento_existe");
        } else {
            header("Location: ../views/auth/register.php?error=desconocido");
        }
        exit();
    }
}