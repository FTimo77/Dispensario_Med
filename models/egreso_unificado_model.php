<?php
require_once __DIR__ . '/../config/conexion.php';

class EgresoUnificadoModel {
    private $conn;
    public function getConnection() {
        return $this->conn;
    }
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }

    public function getProductos($codigo_bodega_actual) {
        $stmt = $this->conn->prepare("SELECT id_prooducto, NOM_PROD, stock_act_prod, PRESENTACION_PROD FROM producto WHERE estado_prod = 1 and codigo_bodega = ?");
        $stmt->bind_param("s", $codigo_bodega_actual);
        $stmt->execute();
        $res = $stmt->get_result();
        $productos = [];
        while ($row = $res->fetch_assoc()) {
            $productos[] = $row;
        }
        $stmt->close();
        return $productos;
    }

    public function getPacientes() {
        $stmt = $this->conn->prepare("SELECT id_paciente, nombre_paciente, apellido_paciente FROM pacientes WHERE est_paciente = 1");
        $stmt->execute();
        $res = $stmt->get_result();
        $pacientes = [];
        while ($row = $res->fetch_assoc()) {
            $pacientes[] = $row;
        }
        $stmt->close();
        return $pacientes;
    }

    public function close() {
        $this->conn->close();
    }
}
