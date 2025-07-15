<?php
require_once __DIR__ . '/../config/conexion.php';
class IngresoModel {
    private $conn;
    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->connect();
    }
    public function getProductos($codigo_bodega_actual) {
        $stmt = $this->conn->prepare("SELECT p.id_prooducto, p.NOM_PROD, p.unidad, pr.descripcion as PRESENTACION_PROD FROM producto p LEFT JOIN presentacion_prod pr ON p.id_presentacion = pr.id_presentacion WHERE p.estado_prod = 1 and p.codigo_bodega = ?");
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
    public function procesarIngreso($data, $id_usuario_actual) {
        $productos_lote = $data['productoLote'] ?? [];
        $nombres_lote = $data['nombreLote'] ?? [];
        $fechas_fabri = $data['fechaElaboracion'] ?? [];
        $fechas_venc = $data['fechaCaducidad'] ?? [];
        $cantidades = $data['cantidad'] ?? [];
        $referencia = trim($data['referenciaIngreso'] ?? '');
        $total = count($productos_lote);
        $mensaje = "";
        if ($total > 0 && !empty($referencia)) {
            $this->conn->begin_transaction();
            try {
                if ($id_usuario_actual === null) {
                    throw new Exception("ID de usuario no encontrado en la sesión.");
                }
                $stmt_cabecera = $this->conn->prepare("INSERT INTO cabecera (FECHA_TRANSC, MOTIVO, TIPO_TRANSAC) VALUES (?, ?, 'I')");
                $fecha_actual_dt = date('Y-m-d H:i:s');
                $stmt_cabecera->bind_param("ss", $fecha_actual_dt, $referencia);
                if (!$stmt_cabecera->execute()) {
                    throw new Exception("Error al crear la cabecera del ingreso: " . $stmt_cabecera->error);
                }
                $cod_transac_id = $this->conn->insert_id;
                $stmt_cabecera->close();
                $stmt_lote = $this->conn->prepare("INSERT INTO lote (NUM_LOTE, ID_PROODUCTO, FECH_VENC, FECH_FABRI, FECHA_ING, CANTIDAD_LOTE) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_kardex = $this->conn->prepare("INSERT INTO kardex (ID_PROODUCTO, COD_TRANSAC, ID_USUARIO, CANTIDAD) VALUES (?, ?, ?, ?)");
                $fecha_ing_lote = date('Y-m-d');
                for ($i = 0; $i < $total; $i++) {
                    $id_producto = $productos_lote[$i];
                    $num_lote = $nombres_lote[$i];
                    $fech_fabri_str = $fechas_fabri[$i] . "-01";
                    $fech_venc_str = $fechas_venc[$i] . "-01";
                    $cantidad_ingresada = (int)$cantidades[$i];
                    $fechaElaboracionDT = new DateTime($fech_fabri_str);
                    $fechaVencimientoDT = new DateTime($fech_venc_str);
                    if ($fechaVencimientoDT < $fechaElaboracionDT) {
                        throw new Exception("Lote '{$num_lote}': La fecha de caducidad no puede ser anterior a la de elaboración.");
                    }
                    $stmt_lote->bind_param("sisssi", $num_lote, $id_producto, $fech_venc_str, $fech_fabri_str, $fecha_ing_lote, $cantidad_ingresada);
                    if (!$stmt_lote->execute()) {
                        throw new Exception("Error al insertar el lote '{$num_lote}': " . $stmt_lote->error);
                    }
                    $this->conn->query("UPDATE producto SET stock_act_prod = stock_act_prod + $cantidad_ingresada WHERE id_prooducto = $id_producto");
                    if ($this->conn->affected_rows === 0) {
                        throw new Exception("Producto {$id_producto} no encontrado o stock no pudo ser actualizado.");
                    }
                    $stmt_kardex->bind_param("iiii", $id_producto, $cod_transac_id, $id_usuario_actual, $cantidad_ingresada);
                    if (!$stmt_kardex->execute()) {
                        throw new Exception("Error al registrar el ingreso en kardex: " . $stmt_kardex->error);
                    }
                }
                $stmt_lote->close();
                $stmt_kardex->close();
                $this->conn->commit();
                $mensaje = '<div class="alert alert-success text-center">Ingreso procesado correctamente.</div>';
            } catch (Exception $e) {
                $this->conn->rollback();
                $mensaje = '<div class="alert alert-danger text-center"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            $mensaje = '<div class="alert alert-warning text-center">Debe agregar lotes y especificar una referencia.</div>';
        }
        return $mensaje;
    }
    public function close() {
        $this->conn->close();
    }
}
