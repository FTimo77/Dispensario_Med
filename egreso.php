<?php
// filepath: d:\Instituto\Prácticas\Respaldo\egreso.php
session_start();

if (!isset($_SESSION['usuario'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
require_once 'config/conexion.php';

$productos = [];
$conexion = new Conexion();
$conn = $conexion->connect();
$res_prod = $conn->query("SELECT id_prooducto, NOM_PROD, stock_act_prod FROM producto WHERE estado_prod = 1");
if ($res_prod) {
    while ($row = $res_prod->fetch_assoc()) {
        $productos[] = $row;
    }
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productos_egreso = $_POST['productoEgreso'] ?? [];
    $cantidades = $_POST['cantidadEgreso'] ?? [];
    $paciente = trim($_POST['paciente'] ?? '');
    $total = count($productos_egreso);
    $id_usuario_actual = $_SESSION['id_usuario'] ?? null;

    if ($total > 0 && !empty($paciente)) {
        $conn->begin_transaction();
        try {
            if ($id_usuario_actual === null) {
                throw new Exception("ID de usuario no encontrado en la sesión.");
            }

            // 1. Crear la transacción en la tabla cabecera
            $stmt_cabecera = $conn->prepare("INSERT INTO cabecera (FECHA_TRANSC, PACIENTE, TIPO_TRANSAC) VALUES (?, ?, 'EGRESO')");
            $fecha_actual = date('Y-m-d H:i:s');
            $stmt_cabecera->bind_param("ss", $fecha_actual, $paciente);
            if (!$stmt_cabecera->execute()) {
                throw new Exception("Error al crear la cabecera de la transacción: " . $stmt_cabecera->error);
            }
            // 2. Obtener el ID numérico (COD_TRANSAC) recién creado
            $cod_transac_id = $conn->insert_id;
            $stmt_cabecera->close();

            // Preparar las consultas para el bucle
            $stmt_update_stock = $conn->prepare("UPDATE producto SET stock_act_prod = ? WHERE id_prooducto = ?");
            $stmt_insert_kardex = $conn->prepare("INSERT INTO kardex (ID_PROODUCTO, COD_TRANSAC, ID_USUARIO, CANTIDAD) VALUES (?, ?, ?, ?)");

            for ($i = 0; $i < $total; $i++) {
                $id_producto = (int)$productos_egreso[$i];
                $cantidad_egresada = (int)$cantidades[$i];

                $stock_res = $conn->query("SELECT stock_act_prod FROM producto WHERE id_prooducto = $id_producto FOR UPDATE");
                if (!$stock_res || $stock_res->num_rows === 0) throw new Exception("Producto no encontrado.");

                $stock_anterior = (int)$stock_res->fetch_assoc()['stock_act_prod'];
                if ($stock_anterior < $cantidad_egresada) throw new Exception("Stock insuficiente para el producto.");

                // Actualizar stock
                $stock_nuevo = $stock_anterior - $cantidad_egresada;
                $stmt_update_stock->bind_param("ii", $stock_nuevo, $id_producto);
                if (!$stmt_update_stock->execute()) throw new Exception("Error al actualizar stock: " . $stmt_update_stock->error);

                // 3. Registrar en Kardex usando el ID numérico de la cabecera
                $stmt_insert_kardex->bind_param("iiii", $id_producto, $cod_transac_id, $id_usuario_actual, $cantidad_egresada);
                if (!$stmt_insert_kardex->execute()) {
                    throw new Exception("Error al registrar en kardex: " . $stmt_insert_kardex->error);
                }
            }

            $stmt_update_stock->close();
            $stmt_insert_kardex->close();
            $conn->commit();
            $mensaje = '<div class="alert alert-success text-center">Egreso procesado correctamente.</div>';

        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = '<div class="alert alert-danger text-center"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning text-center">Debe agregar productos y especificar el nombre del paciente.</div>';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Egreso de Productos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="./assets/icons/capsule-pill.svg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  </head>
  <body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 px-3 py-2 rounded" style="background: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
          Egreso de Productos
        </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEgreso">
          <i class="bi bi-plus-circle"></i> Agregar Egreso
        </button>
      </div>
      <?php if ($mensaje) echo $mensaje; ?>
      <form method="POST" id="formEgresos">
        <!-- CAMBIO: Campo para el nombre del paciente -->
        <div class="mb-3">
            <label for="paciente" class="form-label fw-bold">Nombre del Paciente</label>
            <input type="text" class="form-control" id="paciente" name="paciente" required placeholder="Ingrese el nombre completo del paciente">
        </div>

        <div class="card shadow-sm">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Nombre del Producto</th>
                    <th>Cantidad</th>
                    <th>Lote</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaEgresos">
                  <!-- Egresos agregados dinámicamente -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-send"></i> Enviar todos los egresos
          </button>
        </div>
      </form>
    </div>

    <!-- Modal para agregar egreso -->
    <div class="modal fade" id="modalAgregarEgreso" tabindex="-1" aria-labelledby="modalAgregarEgresoLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" id="formAgregarEgreso" autocomplete="off" onsubmit="agregarEgreso(event)">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarEgresoLabel">Agregar Egreso</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="productoEgreso" class="form-label">Producto</label>
              <select class="form-select" id="productoEgreso" required>
                <option value="" disabled selected>Seleccione un producto</option>
                <?php foreach ($productos as $prod): ?>
                  <option value="<?= htmlspecialchars($prod['id_prooducto']) ?>" data-stock="<?= htmlspecialchars($prod['stock_act_prod']) ?>">
                    <?= htmlspecialchars($prod['NOM_PROD']) ?> (Stock: <?= htmlspecialchars($prod['stock_act_prod']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <!-- carga lote -->
           <div class="mb-3">
            <label for="loteEgreso" class="form-label">Lote del producto</label>
            <select class="form-select" id="loteEgreso" name="loteEgreso" required disabled>
              <option value="" disabled selected>Primero seleccione un producto</option>
            </select>
          </div>
            <div class="mb-3">
              <label for="cantidadEgreso" class="form-label">Cantidad</label>
              <input type="number" class="form-control" id="cantidadEgreso" required min="1"/>
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
      let egresos = [];

      function renderEgresos() {
        const tbody = document.getElementById("tablaEgresos");
        tbody.innerHTML = "";
        egresos.forEach((e, i) => {
          tbody.innerHTML += `
            <tr>
              <td>
                <input type="hidden" name="productoEgreso[]" value="${e.productoId}">
                <input type="hidden" name="cantidadEgreso[]" value="${e.cantidad}">
                <input type="hidden" name="loteEgreso[]" value="${e.loteId}">
                ${i + 1}
              </td>
              <td>${e.productoNombre}</td>
              <td>${e.cantidad}</td>
              <td>${e.loteNombre}</td>
              <td>
                <button class="btn btn-sm btn-outline-danger" title="Eliminar" type="button" onclick="eliminarEgreso(${i})">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
      }

      function agregarEgreso(event) {
        event.preventDefault();
        const productoSelect = document.getElementById("productoEgreso");
        const cantidadInput = document.getElementById("cantidadEgreso");

        const productoId = productoSelect.value;
        const productoNombre = productoSelect.options[productoSelect.selectedIndex].text.split(' (Stock:')[0];
        const stockDisponible = parseInt(productoSelect.options[productoSelect.selectedIndex].getAttribute('data-stock'), 10);
        const cantidad = parseInt(cantidadInput.value, 10);

        


        if (!productoId || !cantidad) {
          alert("Por favor, seleccione un producto y especifique la cantidad.");
          return;
        }
        if (cantidad > stockDisponible) {
          alert(`Stock insuficiente. Solo hay ${stockDisponible} unidades disponibles.`);
          return;
        }

        //lote info
        const selectLote = document.getElementById("loteEgreso");

        // Obtener valores
        const loteId = selectLote.value; // ID del lote (value del option)
        const loteNombre = selectLote.options[selectLote.selectedIndex].text; // Texto visible

        console.log("ID Lote seleccionado:", loteId);
        console.log("Texto completo:", loteNombre);

        egresos.push({ productoId, productoNombre, cantidad, loteId,loteNombre });
        renderEgresos();
        document.getElementById("formAgregarEgreso").reset();
        var modal = bootstrap.Modal.getInstance(document.getElementById("modalAgregarEgreso"));
        modal.hide();
      }

      function eliminarEgreso(idx) {
        if (confirm("¿Seguro que desea eliminar este egreso?")) {
          egresos.splice(idx, 1);
          renderEgresos();
        }
      }

      document.getElementById("formEgresos").addEventListener("submit", function(e) {
        if (egresos.length === 0) {
          alert("Agregue al menos un egreso antes de enviar.");
          e.preventDefault();
        }
        if (document.getElementById('paciente').value.trim() === '') {
            alert('El nombre del paciente es obligatorio.');
            e.preventDefault();
        }
      });

      renderEgresos();

      //obtiene lote y lo carga dinamicamente segun el producto seleccionado
     function cargarLote() {
    document.getElementById("productoEgreso").addEventListener("change", async function() {
        const productoId = this.value;
        const selectLote = document.getElementById("loteEgreso");
        
        // Resetear el select
        selectLote.innerHTML = '<option value="" disabled selected>Cargando lotes...</option>';
        selectLote.disabled = true;

        if (productoId) {
            try {
                const response = await fetch(`/dispensario_med/includes/lote_model.php?id_producto=${productoId}`);
                const lotes = await response.json();
                
                selectLote.innerHTML = ''; // Limpiar opciones
                
                if (lotes.length > 0) {
                    // Agregar opción por defecto
                    const defaultOption = document.createElement("option");
                    defaultOption.value = "";
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    defaultOption.textContent = "Seleccione un lote";
                    selectLote.appendChild(defaultOption);
                    
                    // Agregar lotes
                    lotes.forEach(lote => {
                        const option = document.createElement("option");
                        option.value = lote.id_lote;
                        // Ajusta según los campos de tu respuesta
                        option.textContent = `${lote.NUM_LOTE} stock (${lote.CANTIDAD_LOTE})`;
                        option.value=`${lote.NUM_LOTE}`
                        selectLote.appendChild(option);
                    });
                    selectLote.disabled = false;
                } else {
                    selectLote.innerHTML = '<option value="" disabled>No hay lotes disponibles</option>';
                }
            } catch (error) {
                console.error("Error:", error);
                selectLote.innerHTML = '<option value="" disabled>Error al cargar lotes</option>';
            }
        } else {
            selectLote.innerHTML = '<option value="" disabled selected>Primero seleccione un producto</option>';
        }
    });
}

// Inicializar
cargarLote();

      

    </script>
  </body>
</html>
