<?php
require_once '../controllers/rol_controller.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Gestión de Roles</title>
  <link rel="icon" href="../assets/icons/capsule-pill.svg" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .btn_editar {
      color: green;
      cursor: pointer;
    }

    .btn_eliminar {
      color: red;
    }
  </style>
</head>

<body class="bg-light">
  <?php include '../includes/navbar.php'; ?>

  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 px-3 py-2 rounded"
        style="background: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">Gestión de Roles</h2>
      <button class="btn btn-primary" onclick="abrirModalRol('agregar')">
        <i class="bi bi-plus-circle"></i> Agregar Rol
      </button>
    </div>

    <?php if ($mensaje)
      echo $mensaje; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre del Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($roles) {
                foreach ($roles as $rol) {
                  $estado = $rol['ESTADO_ROL'] == '1' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($rol['COD_ROL']) . "</td>";
                  echo "<td>" . htmlspecialchars($rol['NOMBRE_ROL']) . "</td>";
                  echo "<td>" . $estado . "</td>";
                  echo "<td>
                            <a class='btn_editar' onclick=\"abrirModalRol('editar', '"
                    . htmlspecialchars($rol['COD_ROL']) . "', '"
                    . htmlspecialchars($rol['NOMBRE_ROL']) . "', '"
                    . htmlspecialchars($rol['ESTADO_ROL']) . "')\" title='Editar'>
                              <i class='bi bi-pencil-square'></i>
                            </a>
                            <a class='btn_eliminar ms-2' href='?eliminar=" . htmlspecialchars($rol['COD_ROL']) . "' onclick=\"return confirm('¿Estás seguro que deseas desactivar este rol?')\" title='Eliminar'>
                              <i class='bi bi-trash3-fill'></i>
                            </a>
                          </td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='4' class='text-center'>No hay roles para mostrar.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para agregar/editar rol -->
  <div class="modal fade" id="modalGestionRol" tabindex="-1" aria-labelledby="modalGestionRolLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="formGestionRol" autocomplete="off" method="POST" action="rol_user.php">
        <div class="modal-header">
          <h5 class="modal-title" id="modalGestionRolLabel">Gestionar Rol</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id_rol" name="id_rol">
          <div class="mb-3">
            <label for="nombre_rol" class="form-label">Nombre del Rol</label>
            <input type="select" class="form-control" id="nombre_rol" name="nombre_rol" placeholder="Ej: Administrador"
              required  oninput="mayusculas(this)"/>
          </div>
          <!-- *** CAMBIO: Se agregó un ID para poder ocultarlo/mostrarlo *** -->
          <div class="mb-3" id="campoEstadoRol">
            <label class="form-label">Estado</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="estado_rol" id="estadoActivo" value="1" checked>
                <label class="form-check-label" for="estadoActivo">Activo</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="estado_rol" id="estadoInactivo" value="0">
                <label class="form-check-label" for="estadoInactivo">Inactivo</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/navbar-submenu.js"></script>
  <script src="../js/models.js"></script>
  <script src="../js/valitationInputs.js"></script><!--valida inputs -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const modalEl = document.getElementById('modalGestionRol');
    const modal = new bootstrap.Modal(modalEl);
    const modalLabel = document.getElementById('modalGestionRolLabel');
    const form = document.getElementById('formGestionRol');
    const idRolInput = document.getElementById('id_rol');
    const nombreRolInput = document.getElementById('nombre_rol');
    // *** CAMBIO: Se obtiene el div del campo de estado ***
    const campoEstado = document.getElementById('campoEstadoRol');

    function abrirModalRol(modo, id = '', nombre = '', estado = '1') {
      form.reset();
      idRolInput.value = id;
      nombreRolInput.value = nombre;

      // *** CAMBIO: Lógica para mostrar/ocultar el campo de estado ***
      if (modo === 'editar') {
        modalLabel.textContent = 'Editar Rol';
        campoEstado.style.display = 'block'; // Muestra el campo de estado
        if (estado === '1') {
          document.getElementById('estadoActivo').checked = true;
        } else {
          document.getElementById('estadoInactivo').checked = true;
        }
      } else { // modo 'agregar'
        modalLabel.textContent = 'Agregar Rol';
        campoEstado.style.display = 'none'; // Oculta el campo de estado
      }
      modal.show();
    }
  </script>

</body>

</html>