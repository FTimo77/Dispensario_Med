<?php
require_once '../config/conexion.php';

if(isset($_GET['id_producto'])) {
    $id_producto = (int)$_GET['id_producto'];
    $conexion = new Conexion();
    $conn = $conexion->connect();
    
    // Debug: Verifica conexión
    if ($conn->connect_error) {
        die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
    }
    
    $query = "SELECT * FROM lote WHERE ID_PROODUCTO = ?  AND CANTIDAD_LOTE > 0 ORDER BY FECH_VENC";
    $stmt = $conn->prepare($query);
    
    // Debug: Si prepare falla
    if ($stmt === false) {
        die(json_encode(["error" => "Error en prepare: " . $conn->error]));
    }
    
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lotes = [];
    while($row = $result->fetch_assoc()) {
        $lotes[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($lotes);
    exit;
}

echo json_encode([]);
?>