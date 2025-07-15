<?php include __DIR__ . '/../controllers/ingreso_controller.php'; ?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Gestión de Lotes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  </head>
  <body class="bg-light">
    <?php include '../includes/navbar.php'; ?>
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
            <input type="text" class="form-control uppercase-input" id="referenciaIngreso" name="referenciaIngreso" required placeholder="Ingrese una referencia para el ingreso" oninput="this.value = this.value.toUpperCase()" />
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
                    <?= htmlspecialchars($prod['PRESENTACION_PROD']) ?> -
                    <?= htmlspecialchars($prod['unidad']) ?>
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

    <script src="../js/navbar-submenu.js"></script>
    <script src="../js/models.js"></script>
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