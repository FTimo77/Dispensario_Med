<?php
//valida sesion
session_start();

if (!isset($_SESSION['usuario'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
require_once 'config/conexion.php';

// Obtener productos activos para el select
$productos = [];
$conexion = new Conexion();
$conn = $conexion->connect();

$codigo_bodega_actual = $_SESSION['bodega'] ?? 0; // Obtener la bodega de la sesión
$stmt_prod = $conn->prepare("SELECT id_prooducto, NOM_PROD FROM producto WHERE estado_prod = 1 and codigo_bodega = ?");
$stmt_prod->bind_param("s", $codigo_bodega_actual); // 'i' porque el código de bodega es un entero
$stmt_prod->execute();
$res_prod = $stmt_prod->get_result();

if ($res_prod) {
    while ($row = $res_prod->fetch_assoc()) {
        $productos[] = $row;
    }
}
$stmt_prod->close();

$mensaje = "";

// Procesar ingreso de lotes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productos_lote = $_POST['productoLote'] ?? [];
    $nombres_lote = $_POST['nombreLote'] ?? [];
    $fechas_fabri = $_POST['fechaElaboracion'] ?? [];
    $fechas_venc = $_POST['fechaCaducidad'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $referencia = trim($_POST['referenciaIngreso'] ?? '');
    $total = count($productos_lote);
    $id_usuario_actual = $_SESSION['id_usuario'] ?? null;

    if ($total > 0 && !empty($referencia)) {
        $conn->begin_transaction();
        try {
            if ($id_usuario_actual === null) {
                throw new Exception("ID de usuario no encontrado en la sesión.");
            }

            // 1. Crear la transacción en la tabla cabecera
            $stmt_cabecera = $conn->prepare("INSERT INTO cabecera (FECHA_TRANSC, MOTIVO, TIPO_TRANSAC) VALUES (?, ?, 'I')");
            $fecha_actual_dt = date('Y-m-d H:i:s');
            // Usamos el campo PACIENTE para la referencia, ya que es un campo de texto genérico
            $stmt_cabecera->bind_param("ss", $fecha_actual_dt, $referencia);
            if (!$stmt_cabecera->execute()) {
                throw new Exception("Error al crear la cabecera del ingreso: " . $stmt_cabecera->error);
            }
            // 2. Obtener el ID numérico (COD_TRANSAC) recién creado
            $cod_transac_id = $conn->insert_id;
            $stmt_cabecera->close();

            // Preparar consultas para el bucle
            $stmt_lote = $conn->prepare("INSERT INTO lote (NUM_LOTE, ID_PROODUCTO, FECH_VENC, FECH_FABRI, FECHA_ING, CANTIDAD_LOTE) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_kardex = $conn->prepare("INSERT INTO kardex (ID_PROODUCTO, COD_TRANSAC, ID_USUARIO, CANTIDAD) VALUES (?, ?, ?, ?)");
            $fecha_ing_lote = date('Y-m-d');

            for ($i = 0; $i < $total; $i++) {
                $id_producto = $productos_lote[$i];
                $num_lote = $nombres_lote[$i];
                $fech_fabri_str = $fechas_fabri[$i] . "-01";
                $fech_venc_str = $fechas_venc[$i] . "-01";
                $cantidad_ingresada = (int)$cantidades[$i];

                // Validaciones de fechas (como ya las tenías)
                $fechaElaboracionDT = new DateTime($fech_fabri_str);
                $fechaVencimientoDT = new DateTime($fech_venc_str);
                if ($fechaVencimientoDT < $fechaElaboracionDT) {
                    throw new Exception("Lote '{$num_lote}': La fecha de caducidad no puede ser anterior a la de elaboración.");
                }

                // 3. Insertar en la tabla lote
                $stmt_lote->bind_param("sisssi", $num_lote, $id_producto, $fech_venc_str, $fech_fabri_str, $fecha_ing_lote, $cantidad_ingresada);
                if (!$stmt_lote->execute()) {
                    throw new Exception("Error al insertar el lote '{$num_lote}': " . $stmt_lote->error);
                }

                // 4. Actualizar stock del producto (de forma segura)
                $conn->query("UPDATE producto SET stock_act_prod = stock_act_prod + $cantidad_ingresada WHERE id_prooducto = $id_producto");
                if ($conn->affected_rows === 0) {
                     throw new Exception("Producto {$id_producto} no encontrado o stock no pudo ser actualizado.");
                }

                // 5. Registrar en Kardex usando el ID numérico de la cabecera
                $stmt_kardex->bind_param("iiii", $id_producto, $cod_transac_id, $id_usuario_actual, $cantidad_ingresada);
                if (!$stmt_kardex->execute()) {
                    throw new Exception("Error al registrar el ingreso en kardex: " . $stmt_kardex->error);
                }
            }

            $stmt_lote->close();
            $stmt_kardex->close();
            $conn->commit();
            $mensaje = '<div class="alert alert-success text-center">Ingreso procesado correctamente.</div>';

        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = '<div class="alert alert-danger text-center"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning text-center">Debe agregar lotes y especificar una referencia.</div>';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Gestión de Lotes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  </head>
  <body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 px-3 py-2 rounded"
          style="background: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
          Ingreso de inventario
        </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarLote">
          <i class="bi bi-plus-circle"></i> Agregar Lote
        </button>
      </div>
      <?php if ($mensaje) echo $mensaje; ?>
      <form method="POST" id="formLotes">
        <!-- CAMBIO: Campo para la referencia del ingreso -->
        <div class="mb-3">
            <label for="referenciaIngreso" class="form-label fw-bold">Referencia (Proveedor, Factura, etc.)</label>
            <input type="text" class="form-control" id="referenciaIngreso" name="referenciaIngreso" required placeholder="Ingrese una referencia para el ingreso">
        </div>

        <div class="card shadow-sm">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Nombre del Producto</th>
                    <th>Lote</th>
                    <th>Cantidad</th>
                    <th>Elaboración</th>
                    <th>Caducidad</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaLotes">
                  </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
          <button id="btnEnviarLotes" class="btn btn-success" type="submit">
            <i class="bi bi-send"></i> Enviar todos los lotes
          </button>
        </div>
      </form>
    </div>

    <div class="modal fade" id="modalAgregarLote" tabindex="-1" aria-labelledby="modalAgregarLoteLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" id="formAgregarLote" autocomplete="off" onsubmit="agregarLote(event)">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarLoteLabel">Agregar Lote</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="productoLote" class="form-label">Producto</label>
              <select class="form-select" id="productoLote" required>
                <option value="" disabled selected>Seleccione un producto</option>
                <?php foreach ($productos as $prod): ?>
                  <option value="<?= htmlspecialchars($prod['id_prooducto']) ?>">
                    <?= htmlspecialchars($prod['NOM_PROD']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="nombreLote" class="form-label">Lote</label>
              <input type="text" class="form-control" id="nombreLote" required />
            </div>
            <div class="mb-3">
              <label for="cantidad" class="form-label">Cantidad</label>
              <input type="number" class="form-control" id="cantidad" required min="1" />
            </div>
            <div class="mb-3">
              <label for="fechaElaboracion" class="form-label">Fecha de Elaboración</label>
              <input type="month" class="form-control" id="fechaElaboracion" placeholder="YYYY-MM" required />
            </div>
            <div class="mb-3">
              <label for="fechaCaducidad" class="form-label">Fecha de Caducidad</label>
              <input type="month" class="form-control" id="fechaCaducidad" placeholder="YYYY-MM" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Agregar</button>
          </div>
        </form>
      </div>
    </div>

    <script src="js/navbar-submenu.js"></script>
    <script src="js/models.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      let lotes = [];

      function renderLotes() {
        const tbody = document.getElementById("tablaLotes");
        tbody.innerHTML = "";
        lotes.forEach((l, i) => {
          tbody.innerHTML += `
            <tr>
              <td>
                <input type="hidden" name="productoLote[]" value="${l.producto}">
                <input type="hidden" name="nombreLote[]" value="${l.nombre}">
                <input type="hidden" name="fechaElaboracion[]" value="${l.elaboracion}">
                <input type="hidden" name="fechaCaducidad[]" value="${l.caducidad}">
                <input type="hidden" name="cantidad[]" value="${l.cantidad}">
                ${i + 1}
              </td>
              <td>${l.producto_nombre}</td>
              <td>${l.nombre}</td>
              <td>${l.cantidad}</td>
              <td>${l.elaboracion}</td>
              <td>${l.caducidad}</td>
              <td>
                <button class="btn btn-sm btn-outline-danger" title="Eliminar" type="button" onclick="eliminarLote(${i})">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
      }

      function agregarLote(e) {
        e.preventDefault();
        const productoSel = document.getElementById("productoLote");
        const producto = productoSel.value;
        const producto_nombre = productoSel.options[productoSel.selectedIndex].text;
        const nombre = document.getElementById("nombreLote").value.trim();
        const elaboracion = document.getElementById("fechaElaboracion").value;
        const caducidad = document.getElementById("fechaCaducidad").value;
        const cantidad = document.getElementById("cantidad").value;

        if (!producto || !nombre || !elaboracion || !caducidad || !cantidad) {
          alert("Por favor, complete todos los campos.");
          return;
        }

        const fechaElaboracion = new Date(elaboracion + "-01T00:00:00");
        const fechaCaducidad = new Date(caducidad + "-01T00:00:00");
        const fechaActual = new Date();
        fechaActual.setDate(1);
        fechaActual.setHours(0, 0, 0, 0);

        if (fechaElaboracion > fechaActual) {
          alert("La fecha de elaboración no puede ser posterior al mes actual.");
          return;
        }
        if (fechaCaducidad < fechaElaboracion) {
          alert("La fecha de caducidad no puede ser anterior a la fecha de elaboración.");
          return;
        }
        if (fechaCaducidad < fechaActual) {
          alert("La fecha de caducidad no puede ser anterior al mes actual.");
          return;
        }

        lotes.push({ producto, producto_nombre, nombre, elaboracion, caducidad, cantidad });
        renderLotes();
        document.getElementById("formAgregarLote").reset();
        var modal = bootstrap.Modal.getInstance(document.getElementById("modalAgregarLote"));
        modal.hide();
      }

      function eliminarLote(idx) {
        if (confirm("¿Seguro que desea eliminar este lote?")) {
          lotes.splice(idx, 1);
          renderLotes();
        }
      }

      document.getElementById("formLotes").addEventListener("submit", function(e) {
        if (lotes.length === 0) {
          alert("Agregue al menos un lote antes de enviar.");
          e.preventDefault();
        }
        if (document.getElementById('referenciaIngreso').value.trim() === '') {
            alert('El campo de referencia es obligatorio.');
            e.preventDefault();
        }
      });

      renderLotes();
    </script>
  </body>
</html>