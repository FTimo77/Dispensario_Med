<?php
require_once __DIR__ . '/../config/conexion.php';
class UsuarioModel {
    private $conn;
    public $mensaje = '';
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function obtenerUsuarios($soloActivos = true) {
        $usuarios = [];
        $sql = "SELECT u.ID_USUARIO, u.NOMBRE_USUARIO, u.COD_ROL, u.ESTADO_USUARIO, r.NOMBRE_ROL FROM usuario u LEFT JOIN rol_usuario r ON u.COD_ROL = r.COD_ROL";
        if ($soloActivos) {
            $sql .= " WHERE u.ESTADO_USUARIO = 1";
        }
        $result = $this->conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }
    public function obtenerRoles() {
        $roles = [];
        $result = $this->conn->query("SELECT COD_ROL, NOMBRE_ROL FROM rol_usuario WHERE ESTADO_ROL = 1");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row;
            }
        }
        return $roles;
    }
    public function insertar($cod_rol, $nombre_usuario, $pass_usuario, $estado) {
        $pass_usuario_hash = password_hash($pass_usuario, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuario (COD_ROL, NOMBRE_USUARIO, PASS_USUARIO, ESTADO_USUARIO) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $this->mensaje = 'Error en prepare: ' . $this->conn->error;
            return false;
        }
        $stmt->bind_param("issi", $cod_rol, $nombre_usuario, $pass_usuario_hash, $estado);
        $res = $stmt->execute();
        if (!$res) {
            $this->mensaje = 'Error al insertar usuario: ' . $stmt->error;
        }
        $stmt->close();
        return $res;
    }
    public function editar($id_usuario, $cod_rol, $nombre_usuario, $pass_usuario, $estado) {
        if ($pass_usuario !== '') {
            $stmt = $this->conn->prepare("UPDATE usuario SET COD_ROL=?, NOMBRE_USUARIO=?, PASS_USUARIO=?, ESTADO_USUARIO=? WHERE ID_USUARIO=?");
            if (!$stmt) {
                $this->mensaje = 'Error en prepare: ' . $this->conn->error;
                return false;
            }
            $stmt->bind_param("issii", $cod_rol, $nombre_usuario, $pass_usuario, $estado, $id_usuario);
        } else {
            $stmt = $this->conn->prepare("UPDATE usuario SET COD_ROL=?, NOMBRE_USUARIO=?, ESTADO_USUARIO=? WHERE ID_USUARIO=?");
            if (!$stmt) {
                $this->mensaje = 'Error en prepare: ' . $this->conn->error;
                return false;
            }
            $stmt->bind_param("isii", $cod_rol, $nombre_usuario, $estado, $id_usuario);
        }
        $res = $stmt->execute();
        if (!$res) {
            $this->mensaje = 'Error al editar usuario: ' . $stmt->error;
        }
        $stmt->close();
        return $res;
    }
    public function eliminar($id_usuario) {
        $stmt = $this->conn->prepare("UPDATE usuario SET ESTADO_USUARIO = 0 WHERE ID_USUARIO = ?");
        if (!$stmt) {
            $this->mensaje = 'Error en prepare: ' . $this->conn->error;
            return false;
        }
        $stmt->bind_param("i", $id_usuario);
        $res = $stmt->execute();
        if (!$res) {
            $this->mensaje = 'Error al eliminar usuario: ' . $stmt->error;
        }
        $stmt->close();
        return $res;
    }
    public function close() {
        $this->conn->close();
    }
}
