<?php
require_once __DIR__ . '/../config/conexion.php';
class ProductoModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function obtenerProductos($bodega) {
        $productos = [];
        $stmt = $this->conn->prepare("SELECT p.ID_PROODUCTO, p.ID_CATEGORIA, p.CODIGO_BODEGA, p.id_presentacion, p.NOM_PROD, p.STOCK_ACT_PROD, p.STOCK_MIN_PROD, p.ESTADO_PROD, p.unidad, c.nombre_cat, pr.descripcion as PRESENTACION_PROD FROM producto p LEFT JOIN categoria c ON p.ID_CATEGORIA = c.ID_CATEGORIA LEFT JOIN presentacion_prod pr ON p.id_presentacion = pr.id_presentacion WHERE p.ESTADO_PROD = 1 AND p.CODIGO_BODEGA = ?");
        if (!$stmt) {
            error_log('Error en prepare: ' . $this->conn->error);
            return $productos;
        }
        $stmt->bind_param("i", $bodega);
        if (!$stmt->execute()) {
            error_log('Error en execute: ' . $stmt->error);
            $stmt->close();
            return $productos;
        }
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        $stmt->close();
        return $productos;
    }
    public function obtenerCategorias() {
        $categorias = [];
        $result = $this->conn->query("SELECT id_categoria, nombre_cat FROM categoria WHERE estado_cat = 1");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
        return $categorias;
    }
    public function obtenerPresentaciones() {
        $presentaciones = [];
        $result = $this->conn->query("SELECT id_presentacion, descripcion FROM presentacion_prod WHERE estado = 1");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $presentaciones[] = $row;
            }
        }
        return $presentaciones;
    }
    public function agregarProducto($nombre, $id_presentacion, $categoria_id, $bodega, $stock_minimo, $unidad = '') {
        $stmt = $this->conn->prepare("INSERT INTO producto (NOM_PROD, id_presentacion, ID_CATEGORIA, CODIGO_BODEGA, ESTADO_PROD, STOCK_MIN_PROD, unidad) VALUES (?, ?, ?, ?, 1, ?, ?)");
        if (!$stmt) {
            error_log('Error en prepare (agregarProducto): ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param("siiiss", $nombre, $id_presentacion, $categoria_id, $bodega, $stock_minimo, $unidad);
        $res = $stmt->execute();
        if (!$res) {
            error_log('Error en execute (agregarProducto): ' . $stmt->error);
        }
        $stmt->close();
        return $res;
    }
    public function actualizarProducto($id, $nombre, $id_presentacion, $categoria_id, $stock_minimo, $unidad = '') {
        $stmt = $this->conn->prepare("UPDATE producto SET NOM_PROD=?, id_presentacion=?, ID_CATEGORIA=?, STOCK_MIN_PROD=?, unidad=? WHERE ID_PROODUCTO=?");
        if (!$stmt) {
            error_log('Error en prepare (actualizarProducto): ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param("siiisi", $nombre, $id_presentacion, $categoria_id, $stock_minimo, $unidad, $id);
        $res = $stmt->execute();
        if (!$res) {
            error_log('Error en execute (actualizarProducto): ' . $stmt->error);
        }
        $stmt->close();
        return $res;
    }
    public function eliminarProducto($id) {
        $stmt = $this->conn->prepare("UPDATE producto SET ESTADO_PROD=0 WHERE ID_PROODUCTO=?");
        if (!$stmt) {
            error_log('Error en prepare (eliminarProducto): ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        if (!$res) {
            error_log('Error en execute (eliminarProducto): ' . $stmt->error);
        }
        $stmt->close();
        return $res;
    }
    public function agregarCategoria($nombre) {
        $stmt = $this->conn->prepare("INSERT INTO categoria (nombre_cat, estado_cat) VALUES (?, 1)");
        $stmt->bind_param("s", $nombre);
        $res = $stmt->execute();
        $id = $this->conn->insert_id;
        $stmt->close();
        return $res ? $id : false;
    }
    public function eliminarCategoria($id) {
        $stmt = $this->conn->prepare("UPDATE categoria SET estado_cat=0 WHERE id_categoria=?");
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function agregarPresentacion($descripcion) {
        $stmt = $this->conn->prepare("INSERT INTO presentacion_prod (descripcion, estado) VALUES (?, 1)");
        $stmt->bind_param("s", $descripcion);
        $res = $stmt->execute();
        $id = $this->conn->insert_id;
        $stmt->close();
        return $res ? $id : false;
    }

    public function eliminarPresentacion($id) {
        $stmt = $this->conn->prepare("UPDATE presentacion_prod SET estado=0 WHERE id_presentacion=?");
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function close() {
        $this->conn->close();
    }
}
