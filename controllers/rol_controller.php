<?php
session_start();
require_once __DIR__ . '/../models/rol_model.php';
if (!isset($_SESSION['usuario'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
$model = new RolModel();
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_rol = $_POST['nombre_rol'];
    $id_rol = $_POST['id_rol'] ?? null;
    if (!empty($id_rol)) {
        $estado_rol = $_POST['estado_rol'];
        if ($model->actualizarRol($id_rol, $nombre_rol, $estado_rol)) {
            $mensaje = '<div class="alert alert-success">Rol actualizado correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">El nombre de rol ya existe o error al actualizar.</div>';
        }
    } else {
        if ($model->insertarRol($nombre_rol, '1')) {
            $mensaje = '<div class="alert alert-success">Rol agregado correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Nombre de rol ya existe o error al agregar.</div>';
        }
    }
}
if (isset($_GET['eliminar'])) {
    $id_rol = intval($_GET['eliminar']);
    if ($model->eliminarRol($id_rol)) {
        header("Location: rol_user.php?mensaje=eliminado");
        exit();
    }
}
$roles = $model->obtenerRoles();
$model->close();
