<?php
session_start();
require_once __DIR__ . '/../models/ingreso_model.php';
$model = new IngresoModel();
$codigo_bodega_actual = $_SESSION['bodega'] ?? 0;
$productos = $model->getProductos($codigo_bodega_actual);
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario_actual = $_SESSION['id_usuario'] ?? null;
    $mensaje = $model->procesarIngreso($_POST, $id_usuario_actual);
}
$model->close();
