<?php
session_start();
require_once '../config/database_connection.php';

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

    $sql = "UPDATE usuarios SET Nombre = ?, Apellido = ?, Username = ?";
    $params = [$first_name, $last_name, $username];
    $types = "sss";

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