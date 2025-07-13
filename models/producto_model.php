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
        $stmt = $this->conn->prepare("SELECT p.ID_PROODUCTO, p.ID_CATEGORIA, p.CODIGO_BODEGA, p.PRESENTACION_PROD, p.NOM_PROD, p.STOCK_ACT_PROD, p.STOCK_MIN_PROD, p.ESTADO_PROD, c.nombre_cat FROM producto p LEFT JOIN categoria c ON p.ID_CATEGORIA = c.ID_CATEGORIA WHERE p.ESTADO_PROD = 1 AND p.CODIGO_BODEGA = ?");
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
        $result = $this->conn->query("SELECT DISTINCT PRESENTACION_PROD FROM producto WHERE PRESENTACION_PROD IS NOT NULL AND PRESENTACION_PROD != ''");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $presentaciones[] = $row['PRESENTACION_PROD'];
            }
        }
        return $presentaciones;
    }
    public function agregarProducto($nombre, $presentacion, $categoria_id, $bodega, $stock_minimo) {
        $stmt = $this->conn->prepare("INSERT INTO producto (NOM_PROD, PRESENTACION_PROD, ID_CATEGORIA, CODIGO_BODEGA, ESTADO_PROD, STOCK_MIN_PROD) VALUES (?, ?, ?, ?, 1, ?)");
        if (!$stmt) {
            error_log('Error en prepare (agregarProducto): ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssiii", $nombre, $presentacion, $categoria_id, $bodega, $stock_minimo);
        $res = $stmt->execute();
        if (!$res) {
            error_log('Error en execute (agregarProducto): ' . $stmt->error);
        }
        $stmt->close();
        return $res;
    }
    public function actualizarProducto($id, $nombre, $presentacion, $categoria_id, $stock_minimo) {
        $stmt = $this->conn->prepare("UPDATE producto SET NOM_PROD=?, PRESENTACION_PROD=?, ID_CATEGORIA=?, STOCK_MIN_PROD=? WHERE ID_PROODUCTO=?");
        if (!$stmt) {
            error_log('Error en prepare (actualizarProducto): ' . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssiii", $nombre, $presentacion, $categoria_id, $stock_minimo, $id);
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
    public function close() {
        $this->conn->close();
    }
}
