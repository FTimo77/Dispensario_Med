<?php
//valida sesion
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
require_once 'config/conexion.php';

// Obtener productos activos para el select
$productos = [];
$conexion = new Conexion();
$conn = $conexion->connect();
$res_prod = $conn->query("SELECT id_prooducto, NOM_PROD FROM producto WHERE estado_prod = 1");
if ($res_prod) {
    while ($row = $res_prod->fetch_assoc()) {
        $productos[] = $row;
    }
}

$mensaje = "";

// Procesar ingreso de lotes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productos_lote = $_POST['productoLote'] ?? [];
    $nombres_lote = $_POST['nombreLote'] ?? [];
    $fechas_fabri = $_POST['fechaElaboracion'] ?? [];
    $fechas_venc = $_POST['fechaCaducidad'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $total = count($productos_lote);
    $ok = true;
    $erroresValidacion = []; // Array para almacenar mensajes de error

    if ($total > 0) {
        // Iniciar transacción para asegurar atomicidad
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO lote (NUM_LOTE, ID_PROODUCTO, FECH_VENC, FECH_FABRI, FECHA_ING) VALUES (?, ?, ?, ?, ?)");
            $fecha_ing = date('Y-m-d'); // Fecha de ingreso actual del sistema
            $fechaIngresoSistemaDT = new DateTime($fecha_ing); // Objeto DateTime para comparar

            for ($i = 0; $i < $total; $i++) {
                $id_producto = $productos_lote[$i];
                $num_lote = $nombres_lote[$i];
                $fech_fabri_str = $fechas_fabri[$i] . "-01"; // Convertir YYYY-MM a YYYY-MM-DD
                $fech_venc_str = $fechas_venc[$i] . "-01";   // Convertir YYYY-MM a YYYY-MM-DD
                $cantidad_ingresada = (int)$cantidades[$i];

                // Convertir fechas a objetos DateTime para una mejor manipulación y comparación
                $fechaElaboracionDT = new DateTime($fech_fabri_str);
                $fechaVencimientoDT = new DateTime($fech_venc_str);
                $fechaActualDT = new DateTime();
                // Ajustar la fecha actual a solo año y mes para la comparación de elaboración vs actual si es necesario,
                // o usar directamente la fecha completa si se considera que solo el mes cuenta para la validación.
                // Para consistencia con el JS, ajustamos al primer día del mes actual.
                $fechaActualDT->setTime(0, 0, 0);
                $fechaActualDT->setDate($fechaActualDT->format('Y'), $fechaActualDT->format('m'), 1);

                // --- Validaciones en el servidor ---
                // Validación 1: Fecha de elaboración no debe ser mayor a la fecha actual
                if ($fechaElaboracionDT > $fechaActualDT) {
                    throw new Exception("El lote '{$num_lote}': La fecha de elaboración ({$fech_fabri_str}) no puede ser posterior a la fecha actual.");
                }

                // Validación 2: Fecha de vencimiento no debe ser menor a la fecha de elaboración
                if ($fechaVencimientoDT < $fechaElaboracionDT) {
                    throw new Exception("El lote '{$num_lote}': La fecha de caducidad ({$fech_venc_str}) no puede ser anterior a la fecha de elaboración ({$fech_fabri_str}).");
                }

                // Validación 3: Fecha de vencimiento no debe ser menor a la fecha de ingreso al sistema
                // Aquí usamos $fechaIngresoSistemaDT que es la fecha exacta del día en que se procesa el formulario.
                if ($fechaVencimientoDT < $fechaIngresoSistemaDT) {
                    throw new Exception("El lote '{$num_lote}': La fecha de caducidad ({$fech_venc_str}) no puede ser anterior a la fecha de ingreso al sistema ({$fecha_ing}).");
                }

                // Si todas las validaciones pasan, procede con la inserción y actualización
                $stmt->bind_param("sisss", $num_lote, $id_producto, $fech_venc_str, $fech_fabri_str, $fecha_ing);
                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar el lote '{$num_lote}' en la base de datos: " . $stmt->error);
                }

                // Actualizar stock del producto
                $res_stock = $conn->query("SELECT stock_act_prod FROM producto WHERE id_prooducto = $id_producto");
                if ($res_stock && $res_stock->num_rows > 0) {
                    $row = $res_stock->fetch_assoc();
                    $nuevo_stock = (int)$row['stock_act_prod'] + $cantidad_ingresada;
                    if (!$conn->query("UPDATE producto SET stock_act_prod = $nuevo_stock WHERE id_prooducto = $id_producto")) {
                        throw new Exception("Error al actualizar el stock para el producto {$id_producto} del lote '{$num_lote}': " . $conn->error);
                    }
                } else {
                    throw new Exception("Producto {$id_producto} no encontrado o stock no pudo ser recuperado para el lote '{$num_lote}'.");
                }
            }

            $stmt->close();
            $conn->commit(); // Confirmar todos los cambios si todo fue exitoso
            $mensaje = '<div class="alert alert-success text-center">Lotes ingresados correctamente y stock actualizado.</div>';

        } catch (Exception $e) {
            $conn->rollback(); // Revertir todos los cambios si hubo algún error
            $mensaje = '<div class="alert alert-danger text-center"><strong>Error al procesar los lotes:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            $ok = false; // Asegurar que $ok sea false
        }
    } else {
        $mensaje = '<div class="alert alert-warning text-center">No hay lotes para ingresar.</div>';
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
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Nombre del Producto</th>
                    <th>Lote</th>
                    <th>Fecha de Elaboración</th>
                    <th>Fecha de Caducidad</th>
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
              <label for="fechaElaboracion" class="form-label">Fecha de Elaboración</label>
              <input type="month" class="form-control" id="fechaElaboracion" placeholder="YYYY-MM" required />
            </div>
            <div class="mb-3">
              <label for="fechaCaducidad" class="form-label">Fecha de Caducidad</label>
              <input type="month" class="form-control" id="fechaCaducidad" placeholder="YYYY-MM" required />
            </div>
            <div class="mb-3">
              <label for="cantidad" class="form-label">Cantidad</label>
              <input type="number" class="form-control" id="cantidad" required />
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
      // Lotes en memoria antes de enviar
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
        const elaboracion = document.getElementById("fechaElaboracion").value; // Formato YYYY-MM
        const caducidad = document.getElementById("fechaCaducidad").value; // Formato YYYY-MM
        const cantidad = document.getElementById("cantidad").value;

        // Convertir a objetos Date para comparaciones
        const fechaElaboracion = new Date(elaboracion + "-01"); // Añadir -01 para un día válido
        const fechaCaducidad = new Date(caducidad + "-01"); // Añadir -01 para un día válido
        const fechaActual = new Date();
        // Ajustar fechaActual a YYYY-MM-01 para comparar solo año y mes
        fechaActual.setHours(0, 0, 0, 0);
        fechaActual.setDate(1); // Establecer el día 1 para evitar problemas con fin de mes

        // Validaciones
        if (!producto || !nombre || !elaboracion || !caducidad || !cantidad) {
          alert("Por favor, complete todos los campos.");
          return; // Detener la ejecución si hay campos vacíos
        }

        // Validación 1: Fecha de elaboración no debe ser mayor a la fecha actual
        if (fechaElaboracion > fechaActual) {
          alert(
            "La fecha de elaboración no puede ser posterior a la fecha actual."
          );
          return;
        }

        // Validación 2: Fecha de vencimiento no debe ser menor a la fecha de elaboración
        if (fechaCaducidad < fechaElaboracion) {
          alert(
            "La fecha de caducidad no puede ser anterior a la fecha de elaboración."
          );
          return;
        }

        // Validación 3: Fecha de vencimiento no debe ser menor a la fecha de ingreso al sistema
        // En el lado del cliente, la "fecha de ingreso al sistema" es esencialmente la fecha actual.
        if (fechaCaducidad < fechaActual) {
          alert("La fecha de caducidad no puede ser anterior a la fecha actual.");
          return;
        }

        // Si todas las validaciones pasan, agregar el lote
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

      // Al enviar el formulario principal, si no hay lotes, cancelar envío
      document.getElementById("formLotes").addEventListener("submit", function(e) {
        if (lotes.length === 0) {
          alert("Agregue al menos un lote antes de enviar.");
          e.preventDefault();
        }
      });

      // Inicializar tabla al cargar
      renderLotes();
    </script>
  </body>
</html>