<?php
function obtenerProductos($conn, $idBodega) {
    $productos = [];
    
    // Preparar la consulta con parámetro
    $stmt = $conn->prepare("SELECT p.id_prooducto, p.NOM_PROD, p.PRESENTACION_PROD, c.nombre_cat 
                          FROM producto p 
                          INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                          WHERE p.estado_prod = 1 AND CODIGO_BODEGA = ?");
    
    // Verificar si la preparación fue exitosa
    if ($stmt) {
        // Vincular parámetro (asumiendo que CODIGO_BODEGA es entero)
        $stmt->bind_param("i",  $idBodega);
        
        // Ejecutar consulta
        $stmt->execute();
        
        // Obtener resultados
        $result = $stmt->get_result();
        
        // Procesar resultados
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        
        // Cerrar statement
        $stmt->close();
    } else {
        // Manejar error en la preparación (opcional)
        die("Error al preparar la consulta: " . $conn->error);
    }
    
    return $productos;
}
function agregarProducto($conn, $nombre, $presentacion, $categoria_id, $codigo_bodega, $stock_minimo)
{
    $stmt = $conn->prepare("INSERT INTO producto (NOM_PROD, PRESENTACION_PROD, id_categoria, codigo_bodega, STOCK_MIN_PROD, estado_prod) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssiis", $nombre, $presentacion, $categoria_id, $codigo_bodega, $stock_minimo);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function editarProducto($conn, $id_producto, $nombre, $presentacion, $categoria_id)
{
    $stmt = $conn->prepare("UPDATE producto SET NOM_PROD=?, PRESENTACION_PROD=?, id_categoria=? WHERE id_prooducto=?");
    $stmt->bind_param("ssii", $nombre, $presentacion, $categoria_id, $id_producto);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function eliminarProducto($conn, $id_producto)
{
    $stmt = $conn->prepare("DELETE FROM producto WHERE id_prooducto=?");
    $stmt->bind_param("i", $id_producto);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function obtenerProductoPorId($conn, $id_producto)
{
    $stmt = $conn->prepare("SELECT id_prooducto, NOM_PROD, PRESENTACION_PROD, id_categoria FROM producto WHERE id_prooducto=?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $res = $stmt->get_result();
    $producto = $res->fetch_assoc();
    $stmt->close();
    return $producto;
}