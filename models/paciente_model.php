<?php
require_once __DIR__ . '/../config/conexion.php';
class PacienteModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function obtenerPacientes() {
        $query = "SELECT id_paciente, nombre_paciente, apellido_paciente, empresa, est_paciente FROM pacientes WHERE est_paciente = '1'";
        $resultado = $this->conn->query($query);
        $pacientes = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $pacientes[] = $fila;
            }
        }
        return $pacientes;
    }
    public function obtenerEmpresas() {
        $empresas = [];
        $resultEmpresas = $this->conn->query("SELECT DISTINCT empresa FROM pacientes WHERE empresa IS NOT NULL AND empresa != '' ORDER BY empresa ASC");
        if ($resultEmpresas && $resultEmpresas->num_rows > 0) {
            while ($row = $resultEmpresas->fetch_assoc()) {
                $empresas[] = $row['empresa'];
            }
        }
        return $empresas;
    }
    public function insertar($nombre, $apellido, $empresa, $estado) {
        $stmt = $this->conn->prepare("INSERT INTO pacientes (nombre_paciente, apellido_paciente , empresa, est_paciente) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $apellido, $empresa, $estado);
        return $stmt->execute();
    }
    public function editar($id, $nombre, $apellido, $empresa, $estado) {
        $stmt = $this->conn->prepare("UPDATE pacientes SET nombre_paciente = ?, apellido_paciente = ?, empresa = ?, est_paciente = ? WHERE id_paciente = ?");
        $stmt->bind_param("ssssi", $nombre, $apellido, $empresa, $estado, $id);
        return $stmt->execute();
    }
    public function eliminar($id) {
        $stmt = $this->conn->prepare("UPDATE pacientes SET est_paciente = 0 WHERE id_paciente = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    public function close() {
        $this->conn->close();
    }
}
