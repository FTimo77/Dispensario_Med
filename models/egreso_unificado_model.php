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
        $stmt = $this->conn->prepare("SELECT p.id_prooducto, p.NOM_PROD, p.stock_act_prod, p.unidad, pr.descripcion as PRESENTACION_PROD FROM producto p LEFT JOIN presentacion_prod pr ON p.id_presentacion = pr.id_presentacion WHERE p.estado_prod = 1 and p.codigo_bodega = ? and p.stock_act_prod > 0");
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
