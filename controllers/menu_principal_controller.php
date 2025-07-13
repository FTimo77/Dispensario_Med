<?php
session_start();
require_once __DIR__ . '/../models/lotes_caducar_model.php';
if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("../index.php");
    exit;
}
$codigo_bodega = $_SESSION['bodega'] ?? null;
if ($codigo_bodega === null) {
    die('Error: No se ha definido la bodega en la sesión');
}
$model = new LotesCaducarModel();
try {
    $lotes = $model->obtenerLotesProximosACaducar($codigo_bodega, 9);
} catch (Exception $e) {
    die('<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>');
}
$model->close();
