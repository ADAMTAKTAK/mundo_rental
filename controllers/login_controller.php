<?php
session_start();

require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $connection->prepare("SELECT * FROM usuarios WHERE Username = ? AND Password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['ID_Usuario'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Rol'];

        if ($user['Rol'] == 'Admin') {
            header("Location: ../views/admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        header("Location: ../views/auth/login.php?error=1");
        exit();
    }
}
?>