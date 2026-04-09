<?php
// controllers/login_controller.php
session_start();

// Conectamos a la base de datos
require_once '../config/database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos del formulario
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Preparamos la consulta a la tabla 'usuarios'
    $stmt = $connection->prepare("SELECT * FROM usuarios WHERE Username = ? AND Password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificamos si existe un registro que coincida
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Guardar datos importantes en la sesión global
        $_SESSION['user_id'] = $user['ID_Usuario'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Rol'];

        // Enrutamiento inteligente según el Rol
        if ($user['Rol'] == 'Admin') {
            // Los administradores van a su panel de control
            header("Location: ../views/admin/dashboard.php");
        } else {
            // Los clientes web van a la página principal (Catálogo)
            header("Location: ../index.php");
        }
        exit();
    } else {
        // Credenciales incorrectas, lo devolvemos al login con mensaje de error
        header("Location: ../views/auth/login.php?error=1");
        exit();
    }
}
?>