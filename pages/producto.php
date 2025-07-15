<?php
require_once '../controllers/producto_controller.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crear Producto</title>
  <link rel="icon" href="../assets/icons/capsule-pill.svg" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
  <?php include '../includes/navbar.php'; ?>
  <div class="container py-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 px-3 py-2 rounded"
        style="background: rgba(255,255,255,0.85); box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        Gestión de Productos
      </h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
        <i class="bi bi-plus-circle"></i> Crear Producto
      </button>
    </div>
    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Nombre del Producto</th>
                <th>Presentación</th>
                <th>Unidad</th>
                <th>Categoría</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $i => $prod): ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><?php echo htmlspecialchars($prod['NOM_PROD']); ?></td>
                  <td><?php echo htmlspecialchars($prod['PRESENTACION_PROD']); ?></td>
                  <td><?php echo htmlspecialchars($prod['unidad'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($prod['nombre_cat']); ?></td>
                  <td class="text-end">
                    <!-- Icono Editar -->
                    <button type="button" class="btn btn-sm btn-outline-primary me-2 btn-editar-producto" title="Editar"
                      data-id="<?php echo $prod['ID_PROODUCTO']; ?>"
                      data-nombre="<?php echo htmlspecialchars($prod['NOM_PROD']); ?>"
                      data-presentacion="<?php echo $prod['id_presentacion'] ?? ''; ?>"
                      data-categoria="<?php echo $prod['ID_CATEGORIA'] ?? ''; ?>"
                      data-stockmin="<?php echo htmlspecialchars($prod['STOCK_MIN_PROD'] ?? ''); ?>"
                      data-unidad="<?php echo htmlspecialchars($prod['unidad'] ?? ''); ?>">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <!-- Icono Eliminar -->
                    <a href="producto.php?eliminar=<?php echo $prod['ID_PROODUCTO']; ?>"
                      class="btn btn-sm btn-outline-danger" title="Eliminar"
                      onclick="return confirm('¿Desea eliminar este producto?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para crear producto -->
  <div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalCrearProductoLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="formularioProducto" method="POST" action="">
        <input type="hidden" id="id_producto_editar" name="id_producto_editar" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCrearProductoLabel">Crear Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="productname" class="form-label">Nombre del producto</label>
              <input type="text" class="form-control uppercase-input" id="productname" name="productname" placeholder="Ej. Paracetamol"
                required oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()" />
            </div>
            <div class="col-12">
              <label for="presentacionproducto" class="form-label">Presentación del producto</label>
              <div class="input-group mb-2">
                <select class="form-control" id="presentacionproducto" name="presentacionproducto">
                  <option value="">Seleccione una presentación</option>
                  <?php foreach ($presentaciones as $pres): ?>
                    <option value="<?php echo htmlspecialchars($pres['id_presentacion']); ?>"><?php echo htmlspecialchars($pres['descripcion']); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#modalPresentaciones">
                  <i class="bi bi-gear"></i>
                </button>
              </div>
              <input type="text" class="form-control mt-2 uppercase-input" id="nueva_presentacion" name="nueva_presentacion"
                placeholder="O escriba una nueva presentación" oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()" />
              <small class="text-muted">Seleccione una presentación existente o escriba una nueva.</small>
            </div>
            <div class="col-12">
              <div class="row">
                <div class="col-md-8">
                  <label for="medida_cantidad" class="form-label">Cantidad de la medida</label>
                  <input type="number" min="0" step="any" class="form-control" id="medida_cantidad" name="medida_cantidad" placeholder="Ej. 500" />
                </div>
                <div class="col-md-4">
                  <label for="medida_unidad" class="form-label">Unidad</label>
                  <select class="form-select" id="medida_unidad" name="medida_unidad">
                    <option value="">Unidad</option>
                    <option value="MG">MG</option>
                    <option value="G">G</option>
                    <option value="ML">ML</option>
                    <option value="L">L</option>
                    <option value="UI">UI</option>
                    <option value="MCG">MCG</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-12">
              <label for="categoriaSeleccionada" class="form-label">Categoría</label>
              <div class="input-group mb-2">
                <select class="form-control" id="categoriaSeleccionada" name="categoriaSeleccionada">
                  <option value="">Seleccione una categoría</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id_categoria']; ?>">
                      <?php echo htmlspecialchars($cat['nombre_cat']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#modalCategorias">
                  <i class="bi bi-gear"></i>
                </button>
              </div>
              <input type="text" class="form-control mt-2 uppercase-input" id="nueva_categoria" name="nueva_categoria"
                placeholder="O escriba una nueva categoría" oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()" />
              <small class="text-muted">Seleccione una categoría existente o escriba una nueva.</small>
            </div>
            <div class="col-12">
              <label for="stockmin" class="form-label">Stock Mínimo</label>
              <input type="number" class="form-control" id="stockmin" name="stockminimo"
                placeholder="Ej. 1" required />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Finalizar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal para administrar categorías (fuera de cualquier otro modal) -->
  <div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCategoriasLabel">Administrar Categorías</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Nombre de la categoría</th>
                  <th class="text-end">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categorias as $cat): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($cat['nombre_cat']); ?></td>
                    <td class="text-end">
                      <a href="producto.php?eliminar_categoria=<?php echo $cat['id_categoria']; ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('¿Desea eliminar esta categoría?');">
                        <i class="bi bi-trash"></i> Eliminar
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal para administrar presentaciones -->
  <div class="modal fade" id="modalPresentaciones" tabindex="-1" aria-labelledby="modalPresentacionesLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPresentacionesLabel">Administrar Presentaciones</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Presentación</th>
                  <th class="text-end">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($presentaciones as $pres): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($pres['descripcion']); ?></td>
                    <td class="text-end">
                      <a href="producto.php?eliminar_presentacion=<?php echo $pres['id_presentacion']; ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('¿Desea eliminar esta presentación?');">
                        <i class="bi bi-trash"></i> Eliminar
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <script src="../js/models.js"></script>
  <script src="../js/navbar-submenu.js"></script>
  <script src="../js/valitationInputs.js"></script><!--valida inputs -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- EDICIÓN DE PRODUCTO ---
    document.querySelectorAll('.btn-editar-producto').forEach(btn => {
      btn.addEventListener('click', function() {
        // Rellenar el modal con los datos del producto
        document.getElementById('id_producto_editar').value = this.dataset.id;
        document.getElementById('productname').value = this.dataset.nombre;
        // Usar el ID de presentación directamente
        document.getElementById('presentacionproducto').value = this.dataset.presentacion;
        document.getElementById('categoriaSeleccionada').value = this.dataset.categoria;
        document.getElementById('stockmin').value = this.dataset.stockmin;
        
        // Manejar la unidad - separar cantidad y unidad
        const unidad = this.dataset.unidad || '';
        if (unidad) {
          const partes = unidad.split(' ');
          if (partes.length >= 2) {
            document.getElementById('medida_cantidad').value = partes[0];
            document.getElementById('medida_unidad').value = partes[1];
          }
        }
        
        document.getElementById('modalCrearProductoLabel').textContent = 'Editar Producto';
        var modal = new bootstrap.Modal(document.getElementById('modalCrearProducto'));
        modal.show();
      });
    });
    // Al cerrar el modal, limpiar el formulario y el modo
    document.getElementById('modalCrearProducto').addEventListener('hidden.bs.modal', function () {
      document.getElementById('formularioProducto').reset();
      document.getElementById('id_producto_editar').value = '';
      document.getElementById('nueva_presentacion').value = '';
      document.getElementById('medida_cantidad').value = '';
      document.getElementById('medida_unidad').value = '';
      document.getElementById('modalCrearProductoLabel').textContent = 'Crear Producto';
    });
  </script>
  <div class="wave-container">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"
      style="display:block; width:100vw; height:auto; margin:0; padding:0;">
      <path fill="#0099ff" fill-opacity="1" d="M0,256L48,261.3C96,267,192,277,288,240C384,203,480,117,576,101.3C672,85,
          768,139,864,144C960,149,1056,107,1152,85.3C1248,64,1344,64,1392,64L1440,64L1440,
          320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,
          576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
    </svg>
  </div>
</body>

</html>