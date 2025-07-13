<?php
session_start();
require_once __DIR__ . '/../models/usuario_model.php';
if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
$model = new UsuarioModel();
// Eliminar usuario
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    $model->eliminar($id_usuario);
    header("Location: users.php");
    exit();
}
// Agregar o editar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nuevoUsuario'];
    $cod_rol = $_POST['nuevoRol'];
    $pass_usuario = $_POST['nuevoPassword'];
    $estado = $_POST['estado'];
    $agregarEditar = $_POST['agregarEditar'];
    if ($agregarEditar == "agregar") {
        $estado = 1;
        if ($model->insertar($cod_rol, $nombre_usuario, $pass_usuario, $estado)) {
            header("Location: users.php");
            exit();
        } else {
            echo "<script>alert('" . $model->mensaje . "');</script>";
        }
    } else if ($agregarEditar == "editar") {
        $id_usuario = $_POST['idUsuario'];
        if ($model->editar($id_usuario, $cod_rol, $nombre_usuario, $pass_usuario, $estado)) {
            header("Location: users.php");
            exit();
        } else {
            echo "<script>alert('" . $model->mensaje . "');</script>";
        }
    }
}
$usuarios = $model->obtenerUsuarios(true);
$roles = $model->obtenerRoles();
$model->close();
