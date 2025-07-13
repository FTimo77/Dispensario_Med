<?php
require_once __DIR__ . '/../config/conexion.php';
class RolModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function obtenerRoles() {
        $roles = [];
        $result = $this->conn->query("SELECT * FROM rol_usuario");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row;
            }
        }
        return $roles;
    }
    public function insertarRol($nombre_rol, $estado_rol = '1') {
        $stmt = $this->conn->prepare("INSERT INTO rol_usuario (NOMBRE_ROL, ESTADO_ROL) VALUES (?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("ss", $nombre_rol, $estado_rol);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
    public function actualizarRol($id_rol, $nombre_rol, $estado_rol) {
        $stmt = $this->conn->prepare("UPDATE rol_usuario SET NOMBRE_ROL = ?, ESTADO_ROL = ? WHERE COD_ROL = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ssi", $nombre_rol, $estado_rol, $id_rol);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
    public function eliminarRol($id_rol) {
        $stmt = $this->conn->prepare("UPDATE rol_usuario SET ESTADO_ROL = '0' WHERE COD_ROL = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id_rol);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
    public function close() {
        $this->conn->close();
    }
}
