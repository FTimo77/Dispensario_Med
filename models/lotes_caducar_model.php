<?php
require_once __DIR__ . '/../config/conexion.php';
class LotesCaducarModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function obtenerLotesProximosACaducar($codigo_bodega, $meses = 9) {
        $sql = "SELECT 
            C.NOMBRE_CAT,
            P.NOM_PROD,
            P.STOCK_ACT_PROD,
            PR.DESCRIPCION,
            L.NUM_LOTE,
            L.FECH_VENC,
            TIMESTAMPDIFF(MONTH, NOW(), L.FECH_VENC) AS 'MESES' 
        FROM 
            lote L
        JOIN 
            producto P ON L.ID_PROODUCTO = P.ID_PROODUCTO 
        JOIN 
            categoria C ON P.ID_CATEGORIA = C.ID_CATEGORIA  
        JOIN 
            presentacion_prod PR ON P.ID_PRESENTACION = PR.ID_PRESENTACION
        WHERE 
            TIMESTAMPDIFF(MONTH, NOW(), L.FECH_VENC) < ? 
            AND P.ESTADO_PROD = 1 
            AND P.CODIGO_BODEGA = ?
            AND L.ESTADO_LOTE = 1
        ";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error en la preparación de la consulta: ' . $this->conn->error);
        }
        $stmt->bind_param("ii", $meses, $codigo_bodega);
        if (!$stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $resultado = $stmt->get_result();
        if ($resultado === false) {
            throw new Exception('Error al obtener resultados: ' . $this->conn->error);
        }
        $lotes = [];
        while ($row = $resultado->fetch_assoc()) {
            $lotes[] = $row;
        }
        $stmt->close();
        return $lotes;
    }
    public function close() {
        $this->conn->close();
    }
}
