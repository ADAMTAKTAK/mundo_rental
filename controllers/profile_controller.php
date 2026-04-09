<?php
// controllers/profile_controller.php
session_start();
require_once '../config/database_connection.php';

// Verificamos que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $new_password = $_POST['password'];

    // 1. Iniciamos la consulta base
    $sql = "UPDATE usuarios SET Nombre = ?, Apellido = ?, Username = ?";
    $params = [$first_name, $last_name, $username];
    $types = "sss";

    // 2. Si el usuario escribió algo en el campo de contraseña, la incluimos
    if (!empty($new_password)) {
        $sql .= ", Password = ?";
        $params[] = $new_password;
        $types .= "s";
    }

    $sql .= " WHERE ID_Usuario = ?";
    $params[] = $user_id;
    $types .= "i";

    try {
        $stmt = $connection->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Actualizamos el nombre en la sesión por si cambió
            $_SESSION['username'] = $username;
            header("Location: ../views/auth/profile.php?success=1");
        } else {
            header("Location: ../views/auth/profile.php?error=1");
        }
    } catch (Exception $e) {
        header("Location: ../views/auth/profile.php?error=1");
    }
    exit();
}