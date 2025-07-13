<?php
require_once __DIR__ . '/../config/conexion.php';
class BodegaModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function getAllActive() {
        $bodegas = [];
        $result = $this->conn->query("SELECT codigo_bodega, descripcion, estado_bodega FROM bodega WHERE estado_bodega = 1");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $bodegas[] = $row;
            }
        }
        return $bodegas;
    }
    public function eliminar($codigo) {
        $stmt = $this->conn->prepare("UPDATE bodega SET estado_bodega=0 WHERE codigo_bodega=?");
        $stmt->bind_param("s", $codigo);
        return $stmt->execute();
    }
    public function crear($descripcion) {
        $stmt = $this->conn->prepare("INSERT INTO bodega (descripcion, estado_bodega) VALUES (?, 1)");
        $stmt->bind_param("s", $descripcion);
        return $stmt->execute();
    }
    public function actualizar($codigo, $descripcion) {
        $stmt = $this->conn->prepare("UPDATE bodega SET descripcion=? WHERE codigo_bodega=?");
        $stmt->bind_param("ss", $descripcion, $codigo);
        return $stmt->execute();
    }
    public function existeDescripcion($descripcion) {
        $stmt = $this->conn->prepare("SELECT 1 FROM bodega WHERE descripcion = ? AND estado_bodega = 1");
        $stmt->bind_param("s", $descripcion);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    public function close() {
        $this->conn->close();
    }
}
