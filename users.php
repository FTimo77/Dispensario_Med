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
  </head>
  <!--Script para cargar la barra de navegación -->
  <script>
    fetch("navbar.html")
      .then((res) => res.text())
      .then((data) => {
        document.getElementById("navbar").innerHTML = data;
      });
  </script>
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
        <button
          class="btn btn-primary"
          data-bs-toggle="modal"
          data-bs-target="#modalAgregarUsuario"
        >
          <i class="bi bi-person-plus"></i> Agregar Usuario
        </button>
      </div>
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Usuario</th>
                  <th>Rol</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tablaUsuarios">
                <!-- Los usuarios se cargarán aquí dinámicamente -->
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
      // Datos de ejemplo (puedes reemplazar por datos reales del backend)
      let usuarios = [
        { usuario: "admin", rol: "Administrador", estado: "Activo" },
        { usuario: "juan", rol: "Usuario", estado: "Activo" },
        { usuario: "maria", rol: "Invitado", estado: "Inactivo" },
      ];

      function renderUsuarios() {
        const tbody = document.getElementById("tablaUsuarios");
        tbody.innerHTML = "";
        usuarios.forEach((u, i) => {
          tbody.innerHTML += `
        <tr>
          <td>${i + 1}</td>
          <td>${u.usuario}</td>
          <td>${u.rol}</td>
          <td>
            <span class="badge ${
              u.estado === "Activo" ? "bg-success" : "bg-secondary"
            }">${u.estado}</span>
          </td>
          <td>
            <a class="btn btn-sm btn-outline-primary me-1" title="Editar" href="#">
              <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarUsuario(${i})">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      `;
        });
      }

      function eliminarUsuario(idx) {
        if (confirm("¿Seguro que desea eliminar este usuario?")) {
          usuarios.splice(idx, 1);
          renderUsuarios();
        }
      }

      document
        .getElementById("formAgregarUsuario")
        .addEventListener("submit", function (e) {
          e.preventDefault();
          const usuario = document.getElementById("nuevoUsuario").value.trim();
          const rol = document.getElementById("nuevoRol").value;
          const estado = document.getElementById("nuevoEstado").value;
          if (usuario && rol && estado) {
            usuarios.push({ usuario, rol, estado });
            renderUsuarios();
            this.reset();
            var modal = bootstrap.Modal.getInstance(
              document.getElementById("modalAgregarUsuario")
            );
            modal.hide();
          }
        });

      // Inicializar tabla al cargar
      renderUsuarios();
    </script>
  </body>
</html>
