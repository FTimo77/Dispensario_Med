<?php
session_start();
require_once __DIR__ . '/../models/bodega_model.php';
$model = new BodegaModel();
$mensaje = isset($_SESSION['mensaje_bodega']) ? $_SESSION['mensaje_bodega'] : "";
unset($_SESSION['mensaje_bodega']);

// Eliminar bodega
if (isset($_GET['eliminar'])) {
    $codigoEliminar = $_GET['eliminar'];
    if ($model->eliminar($codigoEliminar)) {
        $_SESSION['mensaje_bodega'] = '<div class="alert alert-success text-center">Bodega eliminada correctamente.</div>';
    } else {
        $_SESSION['mensaje_bodega'] = '<div class="alert alert-danger text-center">Error al eliminar la bodega.</div>';
    }
    header("Location: ../pages/agregar_bodega.php");
    exit;
}

// Crear bodega
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['codigo_bodega']) && isset($_POST['descripcion_bodega'])) {
    $descripcion = trim($_POST['descripcion_bodega']);
    if ($descripcion !== "") {
        if ($model->existeDescripcion($descripcion)) {
            $_SESSION['mensaje_bodega'] = '<div class="alert alert-warning text-center">Ya existe una bodega con ese nombre.</div>';
        } elseif ($model->crear($descripcion)) {
            $_SESSION['mensaje_bodega'] = '<div class="alert alert-success text-center">Bodega creada correctamente.</div>';
        } else {
            $_SESSION['mensaje_bodega'] = '<div class="alert alert-danger text-center">Error al crear la bodega.</div>';
        }
    } else {
        $_SESSION['mensaje_bodega'] = '<div class="alert alert-warning text-center">Todos los campos son obligatorios.</div>';
    }
    header("Location: ../pages/agregar_bodega.php");
    exit;
}

// Editar bodega
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo_bodega']) && isset($_POST['editar_descripcion_bodega'])) {
    $codigo = $_POST['codigo_bodega'];
    $descripcion = trim($_POST['editar_descripcion_bodega']);
    if ($descripcion !== "") {
        if ($model->actualizar($codigo, $descripcion)) {
            $_SESSION['mensaje_bodega'] = '<div class="alert alert-success text-center">Bodega actualizada correctamente.</div>';
        } else {
            $_SESSION['mensaje_bodega'] = '<div class="alert alert-danger text-center">Error al actualizar la bodega.</div>';
        }
    } else {
        $_SESSION['mensaje_bodega'] = '<div class="alert alert-warning text-center">Todos los campos son obligatorios.</div>';
    }
    header("Location: ../pages/agregar_bodega.php");
    exit;
}

// AJAX: crear bodega
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nueva_bodega'])) {
    header('Content-Type: application/json');
    $nombre = trim($_POST['nueva_bodega']);
    if ($nombre === '') {
        echo json_encode(['success' => false, 'message' => 'El nombre no puede estar vacío']);
        exit;
    }
    if ($model->existeDescripcion($nombre)) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una bodega con ese nombre']);
        exit;
    }
    if ($model->crear($nombre)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
    }
    $model->close();
    exit;
}

// Obtener bodegas activas para la vista
$bodegas = $model->getAllActive();
$model->close();
