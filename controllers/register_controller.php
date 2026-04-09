<?php
session_start();
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibir todos los datos
    $username = trim($_POST['username']);
    $password = $_POST['password']; // En un sistema real deberías usar password_hash()
    $email = trim($_POST['email']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    
    $tipo_doc = $_POST['tipo_doc'];
    $num_doc = trim($_POST['num_doc']);
    $telefono = trim($_POST['telefono']);
    $licencia = trim($_POST['licencia']);
    $rol_defecto = 'Cliente';

    // 2. Iniciar una "Transacción" (Si falla una parte, se cancela todo)
    $connection->begin_transaction();

    try {
        // --- PASO A: Crear el CLIENTE (La persona legal) ---
        $stmt_cliente = $connection->prepare("INSERT INTO clientes (Tipo_Documento, Numero_Documento, Nombre, Apellido, Telefono, Licencia_Conducir, Email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_cliente->bind_param("sssssss", $tipo_doc, $num_doc, $nombre, $apellido, $telefono, $licencia, $email);
        $stmt_cliente->execute();
        
        // ¡LA MAGIA!: Obtenemos el ID del cliente que se acaba de crear en la línea anterior
        $id_cliente_nuevo = $connection->insert_id;

       // --- PASO B: Crear el USUARIO (La cuenta web) ---
        $stmt_usuario = $connection->prepare("INSERT INTO usuarios (Username, Password, Nombre, Apellido, Email, Rol, ID_Cliente) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_usuario->bind_param("ssssssi", $username, $password, $nombre, $apellido, $email, $rol_defecto, $id_cliente_nuevo);
        $stmt_usuario->execute();

        // ¡LA SOLUCIÓN! Capturamos el ID del usuario justo aquí, antes del commit
        $id_usuario_nuevo = $stmt_usuario->insert_id;

        // 3. Si todo salió bien, guardamos los cambios definitivamente
        $connection->commit();

        // 4. Iniciar sesión automáticamente con el ID capturado
        $_SESSION['user_id'] = $id_usuario_nuevo; 
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $rol_defecto;

        // Redirigir al inicio
        header("Location: ../index.php?success=registrado");
        exit();
        
    } catch (mysqli_sql_exception $e) {
        // Si algo falla (ej. el username ya existe, o la cédula está duplicada) deshacemos todo
        $connection->rollback();
        
        // Determinar qué error fue para avisarle al usuario
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