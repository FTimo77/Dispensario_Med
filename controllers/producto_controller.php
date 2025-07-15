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
        $mensaje = "Categoría, producto y presentación agregados correctamente.";
    } elseif ($_GET['success'] == 2) {
        $mensaje = "Producto eliminado correctamente.";
    } elseif ($_GET['success'] == 3) {
        $mensaje = "Categoría eliminada correctamente.";
    } elseif ($_GET['success'] == 4) {
        $mensaje = "Producto actualizado correctamente.";
    } elseif ($_GET['success'] == 5) {
        $mensaje = "Presentación eliminada correctamente.";
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

// Eliminar presentación
if (isset($_GET['eliminar_presentacion'])) {
    $idPresEliminar = intval($_GET['eliminar_presentacion']);
    if ($model->eliminarPresentacion($idPresEliminar)) {
        header("Location: producto.php?success=5");
        exit;
    } else {
        $mensaje = "Error al eliminar la presentación.";
    }
}
// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto_editar = isset($_POST['id_producto_editar']) ? trim($_POST['id_producto_editar']) : '';
    $nombre = trim($_POST['productname']);
    $id_presentacion = trim($_POST['presentacionproducto']);
    $categoria_id = trim($_POST['categoriaSeleccionada']);
    $nueva_categoria = trim($_POST['nueva_categoria']);
    $nueva_presentacion = trim($_POST['nueva_presentacion']);
    $stock_minimo = trim($_POST['stockminimo']);
    $medida_cantidad = trim($_POST['medida_cantidad']);
    $medida_unidad = trim($_POST['medida_unidad']);
    
    // Crear la unidad completa si ambos campos están llenos
    $unidad = '';
    if (!empty($medida_cantidad) && !empty($medida_unidad)) {
        $unidad = $medida_cantidad . ' ' . $medida_unidad;
    }
    
    if ($nombre !== "" && ($id_presentacion !== "" || $nueva_presentacion !== "") && ($categoria_id !== "" || $nueva_categoria !== "")) {
        if ($nueva_categoria !== "") {
            $cat_id = $model->agregarCategoria($nueva_categoria);
            if ($cat_id) {
                $categoria_id = $cat_id;
            } else {
                $mensaje = "Error al crear la categoría.";
            }
        }
        
        if ($nueva_presentacion !== "") {
            $pres_id = $model->agregarPresentacion($nueva_presentacion);
            if ($pres_id) {
                $id_presentacion = $pres_id;
            } else {
                $mensaje = "Error al crear la presentación.";
            }
        }
        
        if ($id_producto_editar !== "") {
            if ($model->actualizarProducto($id_producto_editar, $nombre, $id_presentacion, $categoria_id, $stock_minimo, $unidad)) {
                header("Location: producto.php?success=4");
                exit;
            } else {
                $mensaje = "Error al actualizar el producto.";
            }
        } else {
            if ($categoria_id !== "" && $id_presentacion !== "") {
                if ($model->agregarProducto($nombre, $id_presentacion, $categoria_id, $_SESSION['bodega'], $stock_minimo, $unidad)) {
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
