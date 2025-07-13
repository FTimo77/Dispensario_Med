<?php
require_once __DIR__ . '/../config/conexion.php';

class LoginModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function usuariosCount() {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM usuario");
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return intval($row['total']);
    }
    public function getBodegas() {
        $res = $this->conn->query("SELECT * FROM BODEGA WHERE ESTADO_BODEGA = '1'");
        $bodegas = [];
        while ($fila = $res->fetch_assoc()) {
            $bodegas[] = $fila;
        }
        return $bodegas;
    }
    public function validarUsuario($usuario, $clave) {
        require_once __DIR__ . '/../includes/usuario_model.php';
        return validarUsuario($this->conn, $usuario, $clave);
    }
    public function close() {
        $this->conn->close();
    }
}
