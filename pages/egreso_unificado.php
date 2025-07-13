
<?php include '../controllers/egreso_unificado_controller.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Egreso de Productos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="../assets/icons/capsule-pill.svg" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
  <?php include '../includes/navbar.php'; ?>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 px-3 py-2 rounded"
        style="background: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
        Egreso de Productos
      </h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEgreso">
        <i class="bi bi-plus-circle"></i> Agregar Egreso
      </button>
    </div>
    <?php if ($mensaje)
      echo $mensaje; ?>
    <form method="POST" id="formEgresos">
      <!-- Ingreso del Nombre del paciente -->
      <?php if ($tipo === 'normal'): ?>
      <div class="mb-3 d-flex align-items-end gap-2">
        <div style="flex:1">
          <label for="paciente" class="form-label fw-bold">Nombre del Paciente</label>
          <select name="paciente" id="paciente" class="form-select" required>
            <option value="" disabled selected>Seleccione un paciente</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= htmlspecialchars($p['id_paciente']) ?>">
                <?= htmlspecialchars($p['nombre_paciente'] . ' ' . $p['apellido_paciente']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <a href="paciente.php" class="btn btn-outline-primary mb-1" title="Agregar paciente" style="white-space:nowrap;">
          <i class="bi bi-person-plus"></i> Agregar Paciente
        </a>
      </div>
      <!-- Campo para el motivo del egreso -->
      <div class="mb-3">
        <label for="motivo" class="form-label fw-bold">Motivo del Egreso</label>
        <input type="text" class="form-control uppercase-input" id="motivo" name="motivo" placeholder="Ingrese el motivo del egreso"
          required oninput="this.value = this.value.toUpperCase()" />
      </div>
      <?php endif; ?>

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
  <div class="modal fade" id="modalAgregarEgreso" tabindex="-1" aria-labelledby="modalAgregarEgresoLabel"
    aria-hidden="true">
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
                <option value="<?= htmlspecialchars($prod['id_prooducto']) ?>"
                  data-stock="<?= htmlspecialchars($prod['stock_act_prod']) ?>">
                  <?= htmlspecialchars($prod['NOM_PROD']) ?> <?= htmlspecialchars($prod['PRESENTACION_PROD']) ?> (Stock: <?= htmlspecialchars($prod['stock_act_prod']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- carga lote -->
          <div class="mb-3">
            <label for="loteEgreso" class="form-label">Lote del producto</label>
            <select class="form-select" id="loteEgreso" name="loteEgreso" required disabled>
              <option value="" disabled selected>Seleccione un lote</option>
              <!-- Las opciones se llenarán dinámicamente con JavaScript -->
            </select>
          </div>
          <div class="mb-3">
            <label for="cantidadEgreso" class="form-label">Cantidad</label>
            <input type="number" class="form-control" id="cantidadEgreso" required min="1" />
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
  <script src="../js/egreso_unificado.js"></script>
</body>

</html>