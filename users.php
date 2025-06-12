<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Gestión de Usuarios</title>
       <link rel="icon" href="./assets/icons/capsule-pill.svg" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/style.css" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
      rel="stylesheet"
    />
    <style>
      .btn_editar{
        color: green;
      }
      .btn_eliminar{
        color: red;
      }
    </style>
  </head>
  
  <body class="bg-light">
    <div id="navbar"></div>
    <div class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2
          class="mb-0 px-3 py-2 rounded"
          style="
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
          "
        >
          Usuarios
        </h2>
        <button class="btn btn-primary" onclick="abrirModalAgregar()">
          <i class="bi bi-person-plus"></i> Agregar Usuario
      </button>
      </div>
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
         <table class="table table-hover align-middle">
            <tr>
              <th>ID</th>
              <th>Nombre usuario</th>
              <th>Rol Usuario</th>
              <th>Password</th>
              <th>Estado</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>
          <tbody id="tablaUsuarios">
            <?php
            require_once "./config/conexion.php";
            require_once "./includes/usuario_model.php";
            
            $con = new Conexion();
            $con = $con->connect();
            $usuarios = obtenerUsuarios($con);

            if ($usuarios) {
                foreach ($usuarios as $usuario) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($usuario['ID_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['NOMBRE_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['COD_ROL']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['PASS_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['ESTADO_USUARIO']) . "</td>";
                    echo "<td><a class='btn_editar' href='#' onclick=\"abrirModalEditar('"
                  . htmlspecialchars($usuario['ID_USUARIO']) . "', '"
                  . htmlspecialchars($usuario['NOMBRE_USUARIO']) . "', '"
                  . htmlspecialchars($usuario['COD_ROL']) . "', '"
                  . htmlspecialchars($usuario['ESTADO_USUARIO']) . "')\">
                  <i class='bi bi-pencil-square'></i></a></td>";

                    echo "<td><a class='btn_eliminar' href='#'><i class='bi bi-trash3-fill'></i></a></td>"  ;
                  }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No hay datos</td></tr>";
            }
            ?>
        </tbody>
      </table>

          </div>
        </div>
      </div>
    </div>

    <!-- Modal para agregar usuario -->
    <div
      class="modal fade"
      id="modalAgregarUsuario"
      tabindex="-1"
      aria-labelledby="modalAgregarUsuarioLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <form class="modal-content" id="formAgregarUsuario" autocomplete="off">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAgregarUsuarioLabel">
              Agregar Usuario
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Cerrar"
            ></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="nuevoUsuario" class="form-label">Usuario</label>
              <input
                type="text"
                class="form-control"
                id="nuevoUsuario"
                required
              />
            </div>
            <div class="mb-3">
              <label for="nuevoRol" class="form-label">Rol</label>
              <select class="form-select" id="nuevoRol" required>
                <option value="" disabled selected>Seleccione un rol</option>
                <option value="Administrador">Administrador</option>
                <option value="Usuario">Usuario</option>
                <option value="Invitado">Invitado</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="nuevoEstado" class="form-label">Estado</label>
              <select class="form-select" id="nuevoEstado" required>
                <option value="Activo" selected>Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button type="submit" class="btn btn-success">Agregar</button>
          </div>
        </form>
      </div>
    </div>

    <script src="js/models.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
   
   
      <script>
  const modal = new bootstrap.Modal(document.getElementById('modalAgregarUsuario'));

  function abrirModalAgregar() {
    document.getElementById('formAgregarUsuario').reset();
    document.getElementById('nuevoUsuario').value = '';
    document.getElementById('nuevoRol').value = '';
    document.getElementById('nuevoEstado').value = 'Activo';
    modal.show();
  }

  function abrirModalEditar(id, nombre, rol, estado) {
    document.getElementById('nuevoUsuario').value = nombre;
    document.getElementById('nuevoRol').value = rol;
    document.getElementById('nuevoEstado').value = estado;
    modal.show();

    
  }
</script>


  </body>
</html>
