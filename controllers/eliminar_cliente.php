<?php
session_start();
require_once '../config/database_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_cliente = (int)$_GET['id'];

    $stmt_check = $connection->prepare("SELECT ID_Usuario FROM usuarios WHERE ID_Cliente = ?");
    $stmt_check->bind_param("i", $id_cliente);
    $stmt_check->execute();
    $res = $stmt_check->get_result()->fetch_assoc();

    if ($res && $res['ID_Usuario'] == $_SESSION['user_id']) {
        header("Location: ../views/admin/clientes.php?error=auto_eliminacion");
        exit();
    }

    $connection->begin_transaction();

    try {
        $stmt_user = $connection->prepare("DELETE FROM usuarios WHERE ID_Cliente = ?");
        $stmt_user->bind_param("i", $id_cliente);
        $stmt_user->execute();

        $stmt_cliente = $connection->prepare("DELETE FROM clientes WHERE ID_Cliente = ?");
        $stmt_cliente->bind_param("i", $id_cliente);
        $stmt_cliente->execute();

        $connection->commit();
        header("Location: ../views/admin/clientes.php?success=eliminado");

    } catch (mysqli_sql_exception $e) {
        $connection->rollback();
        header("Location: ../views/admin/clientes.php?error=en_uso");
    }
} else {
    header("Location: ../views/admin/clientes.php");
}
exit();
?>