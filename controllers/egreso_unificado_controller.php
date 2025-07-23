<?php
session_start();
require_once __DIR__ . '/../models/egreso_unificado_model.php';

$tipo = $_GET['tipo'] ?? 'normal'; 
$model = new EgresoUnificadoModel();
$codigo_bodega_actual = $_SESSION['bodega'] ?? 0;
$productos = $model->getProductos($codigo_bodega_actual);
$pacientes = ($tipo === 'normal') ? $model->getPacientes() : [];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productos_egreso = $_POST['productoEgreso'] ?? [];
    $cantidades = $_POST['cantidadEgreso'] ?? [];
    $lotes_egreso = $_POST['loteEgreso'] ?? [];
    $total = count($productos_egreso);
    $id_usuario_actual = $_SESSION['id_usuario'] ?? null;

    if ($tipo === 'botiquin') {
        $paciente = null;
        $motivo = 'Botiquín';
    } elseif($tipo=='dispensario'){
        $paciente = null;
        $motivo = 'Dispensario';
    }
        else {
        $paciente = trim($_POST['paciente'] ?? '');
        $motivo = trim($_POST['motivo'] ?? '');
    }

    $valid = $total > 0 && $id_usuario_actual !== null;
    if ($tipo === 'normal') {
        $valid = $valid && !empty($paciente) && !empty($motivo);
    }

    if ($valid) {
        $conn = $model->getConnection();
        $conn->begin_transaction();
        try {
            $stmt_cabecera = $conn->prepare("INSERT INTO cabecera (FECHA_TRANSC, MOTIVO, id_paciente, TIPO_TRANSAC) VALUES (?, ?, ?, 'E')");
            $fecha_actual = date('Y-m-d H:i:s');
            $stmt_cabecera->bind_param("sss", $fecha_actual, $motivo, $paciente);
            if (!$stmt_cabecera->execute()) {
                throw new Exception("Error al crear la cabecera de la transacción: " . $stmt_cabecera->error);
            }
            $cod_transac_id = $conn->insert_id;
            $stmt_cabecera->close();

            $stmt_update_stock = $conn->prepare("UPDATE producto SET stock_act_prod = ? WHERE id_prooducto = ?");
            $stmt_insert_kardex = $conn->prepare("INSERT INTO kardex (ID_PROODUCTO, COD_TRANSAC, ID_USUARIO, CANTIDAD) VALUES (?, ?, ?, ?)");
            $stmt_update_lote = $conn->prepare("UPDATE lote SET CANTIDAD_LOTE = ? WHERE num_lote = ?");
            $stmt_check_lote = $conn->prepare("SELECT NUM_LOTE, CANTIDAD_LOTE FROM lote WHERE NUM_LOTE = ? AND estado_lote = 1");

            for ($i = 0; $i < $total; $i++) {
                $id_producto = (int)$productos_egreso[$i];
                $cantidad_egresada = (int)$cantidades[$i];
                $num_lote = $lotes_egreso[$i];

                $stmt_check_lote->bind_param("s", $num_lote);
                $stmt_check_lote->execute();
                $lote_res = $stmt_check_lote->get_result();

                if ($lote_res->num_rows === 0) {
                    throw new Exception("Lote '{$num_lote}' no encontrado.");
                }
                $cantidad_lote_anterior = (int)$lote_res->fetch_assoc()['CANTIDAD_LOTE'];

                if ($cantidad_lote_anterior < $cantidad_egresada) {
                    throw new Exception("Stock insuficiente en el lote '{$num_lote}'. Disponibles: {$cantidad_lote_anterior}");
                }

                $nueva_cantidad_lote = $cantidad_lote_anterior - $cantidad_egresada;
                $stmt_update_lote->bind_param("is", $nueva_cantidad_lote, $num_lote);
                if (!$stmt_update_lote->execute()) {
                    throw new Exception("Error al actualizar stock del lote '{$num_lote}': " . $stmt_update_lote->error);
                }

                $stock_res = $conn->query("SELECT stock_act_prod FROM producto WHERE id_prooducto = $id_producto FOR UPDATE");
                if (!$stock_res || $stock_res->num_rows === 0)
                    throw new Exception("Producto no encontrado.");

                $stock_anterior = (int)$stock_res->fetch_assoc()['stock_act_prod'];
                if ($stock_anterior < $cantidad_egresada)
                    throw new Exception("Stock insuficiente para el producto.");

                $stock_nuevo = $stock_anterior - $cantidad_egresada;
                $stmt_update_stock->bind_param("ii", $stock_nuevo, $id_producto);
                if (!$stmt_update_stock->execute())
                    throw new Exception("Error al actualizar stock: " . $stmt_update_stock->error);

                $stmt_insert_kardex->bind_param("iiii", $id_producto, $cod_transac_id, $id_usuario_actual, $cantidad_egresada);
                if (!$stmt_insert_kardex->execute()) {
                    throw new Exception("Error al registrar en kardex: " . $stmt_insert_kardex->error);
                }
            }

            $stmt_update_stock->close();
            $stmt_insert_kardex->close();
            $stmt_update_lote->close();
            $stmt_check_lote->close();
            $conn->commit();
            $mensaje = '<div class="alert alert-success text-center">Egreso procesado correctamente.</div>';

        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = '<div class="alert alert-danger text-center"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning text-center">Debe agregar productos' . ($tipo === 'normal' ? ', especificar el nombre del paciente y el motivo.' : '.') . '</div>';
    }
}
$model->close();
