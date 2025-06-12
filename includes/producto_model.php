<?php

function obtenerProductos($conn)
{
    $productos = [];
    $result = $conn->query("SELECT p.id_prooducto, p.NOM_PROD, p.PRESENTACION_PROD, c.nombre_cat 
                        FROM producto p 
                        INNER JOIN categoria c ON p.id_categoria = c.id_categoria
                        WHERE p.estado_prod = 1");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    return $productos;
}

function agregarProducto($conn, $nombre, $presentacion, $categoria_id)
{
    $stmt = $conn->prepare("INSERT INTO producto (NOM_PROD, PRESENTACION_PROD, id_categoria, estado_prod) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("ssi", $nombre, $presentacion, $categoria_id);
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