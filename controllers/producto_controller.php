<?php
session_start();
require_once __DIR__ . '/../models/producto_model.php';
if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
$model = new ProductoModel();
$mensaje = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $mensaje = "Categoría y producto agregados correctamente.";
    } elseif ($_GET['success'] == 2) {
        $mensaje = "Producto eliminado correctamente.";
    } elseif ($_GET['success'] == 3) {
        $mensaje = "Categoría eliminada correctamente.";
    } elseif ($_GET['success'] == 4) {
        $mensaje = "Producto actualizado correctamente.";
    }
}
// Eliminar producto
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    if ($model->eliminarProducto($idEliminar)) {
        header("Location: producto.php?success=2");
        exit;
    } else {
        $mensaje = "Error al eliminar el producto.";
    }
}
// Eliminar categoría
if (isset($_GET['eliminar_categoria'])) {
    $idCatEliminar = intval($_GET['eliminar_categoria']);
    if ($model->eliminarCategoria($idCatEliminar)) {
        header("Location: producto.php?success=3");
        exit;
    } else {
        $mensaje = "Error al eliminar la categoría.";
    }
}
// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto_editar = isset($_POST['id_producto_editar']) ? trim($_POST['id_producto_editar']) : '';
    $nombre = trim($_POST['productname']);
    $presentacion = trim($_POST['presentacionproducto']);
    $nueva_presentacion = trim($_POST['nueva_presentacion']);
    $medida_cantidad = trim($_POST['medida_cantidad']);
    $medida_unidad = trim($_POST['medida_unidad']);
    $categoria_id = trim($_POST['categoriaSeleccionada']);
    $nueva_categoria = trim($_POST['nueva_categoria']);
    $stock_minimo = trim($_POST['stockminimo']);
    if ($nueva_presentacion !== '') {
        $presentacion = $nueva_presentacion;
    }
    if ($medida_cantidad !== '' && $medida_unidad !== '') {
        $presentacion .= ' - ' . $medida_cantidad . ' ' . $medida_unidad;
    }
    if ($nombre !== "" && $presentacion !== "" && ($categoria_id !== "" || $nueva_categoria !== "")) {
        if ($nueva_categoria !== "") {
            $cat_id = $model->agregarCategoria($nueva_categoria);
            if ($cat_id) {
                $categoria_id = $cat_id;
            } else {
                $mensaje = "Error al crear la categoría.";
            }
        }
        if ($id_producto_editar !== "") {
            if ($model->actualizarProducto($id_producto_editar, $nombre, $presentacion, $categoria_id, $stock_minimo)) {
                header("Location: producto.php?success=4");
                exit;
            } else {
                $mensaje = "Error al actualizar el producto.";
            }
        } else {
            if ($categoria_id !== "") {
                if ($model->agregarProducto($nombre, $presentacion, $categoria_id, $_SESSION['bodega'], $stock_minimo)) {
                    header("Location: producto.php?success=1");
                    exit;
                } else {
                    $mensaje = "Error al crear el producto.";
                }
            }
        }
    } else {
        $mensaje = "Todos los campos son obligatorios.";
    }
}
$categorias = $model->obtenerCategorias();
$presentaciones = $model->obtenerPresentaciones();
$productos = $model->obtenerProductos($_SESSION['bodega']);
$model->close();
