<?php
require_once '../controllers/usuario_controller.php';
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Usuarios</title>
  <link rel="icon" href="../assets/icons/capsule-pill.svg" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .btn_editar { color: green; }
    .btn_eliminar { color: red; }
  </style>
</head>

<body class="bg-light">
<?php include '../includes/navbar.php'; ?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 px-3 py-2 rounded" style="background: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">Usuarios</h2>
    <button class="btn btn-primary" onclick="abrirModalAgregar()">
      <i class="bi bi-person-plus"></i> Agregar Usuario
    </button>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre usuario</th>
              <th>Rol Usuario</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>
          <tbody id="tablaUsuarios">
            <?php
            if ($usuarios) {
                foreach ($usuarios as $usuario) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($usuario['ID_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['NOMBRE_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['NOMBRE_ROL']) . "</td>";
                    echo "<td><a class='btn_editar' href='#' onclick=\"abrirModalEditar('"
                        . htmlspecialchars($usuario['ID_USUARIO']) . "', '"
                        . htmlspecialchars($usuario['NOMBRE_USUARIO']) . "', '"
                        . htmlspecialchars($usuario['NOMBRE_ROL']) . "', '"
                        . htmlspecialchars($usuario['COD_ROL']) . "', '"
                        . htmlspecialchars($usuario['ESTADO_USUARIO']) . "')\">
                        <i class='bi bi-pencil-square'></i></a></td>";
                    echo "<td><a class='btn_eliminar' href='?id_usuario=" . htmlspecialchars($usuario['ID_USUARIO']) . "' onclick=\"return confirm('¿Estás seguro que deseas eliminar este usuario?')\"><i class='bi bi-trash3-fill'></i></a></td>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No hay datos</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  
</div>





<!-- Modal para agregar usuario -->
<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formAgregarUsuario" autocomplete="off" method="POST" action="" onsubmit="return validarFormularioUsuario();">
      <div class="modal-header">
        <h5 class="modal-title" id="abrirModalAgregarLabel">Agregar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="idUsuaro" name="idUsuario" hidden readonly />
          <label for="nuevoRol" class="form-label">Rol</label>
          <select class="form-select" id="nuevoRol" name="nuevoRol" required>
            <option id="rol" value="">Seleccione</option>
            <?php
            if ($roles) {
                foreach ($roles as $rol) {
                    echo '<option value="' . $rol['COD_ROL'] . '">' . htmlspecialchars($rol['NOMBRE_ROL']) . '</option>';
                }
            } else {
                echo "<option>No hay roles disponibles</option>";
            }
            ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="nuevoUsuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="nuevoUsuario" name="nuevoUsuario" placeholder="Ingrese el nombre de usuario" required oninput="soloLetras(this)"/>
        </div>
        <div class="mb-3">
          <label for="nuevoPassword" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="nuevoPassword" name="nuevoPassword" placeholder="Ingrese su nueva contraseña" required />
        </div>
        <div class="mb-3">
          <label for="repetirPassword" class="form-label">Repetir Contraseña</label>
          <input type="password" class="form-control" id="repetirPassword" name="repetirPassword" placeholder="Repita la contraseña" required />
        </div>
        <div class="mb-3" id="divEstado" style="display: none;">
          <label class="form-label">Estado del Usuario</label>
          <div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="estado" id="estadoActivo" value="1">
              <label class="form-check-label" for="estadoActivo">
                Activo
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="estado" id="estadoInactivo" value="0">
              <label class="form-check-label" for="estadoInactivo">
                Inactivo
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="btnAgregarEditar" type="submit" class="btn btn-success" name="agregarEditar" value="agregar">Agregar</button>
      </div>
    </form>
  </div>
</div>

<script src="../js/navbar-submenu.js"></script>
<script src="../js/models.js"></script>
<script src="../js/valitationInputs.js"></script><!--valida inputs -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const modal = new bootstrap.Modal(document.getElementById('modalAgregarUsuario'));

  function abrirModalEditar(id_usuario, nombre, rol, cod_rol, estado) {
    // Cambiar título del modal
    document.getElementById('abrirModalAgregarLabel').innerHTML = 'Editar Usuario';
    
    // Llenar los campos del formulario
    document.getElementById('nuevoUsuario').value = nombre;
    
    // Seleccionar el rol en el dropdown
    document.getElementById('nuevoRol').value = cod_rol;
    
    // Limpiar campos de contraseña
    document.getElementById('nuevoPassword').value = '';
    document.getElementById('repetirPassword').value = '';
    
    // Hacer los campos de contraseña opcionales para edición
    document.getElementById('nuevoPassword').required = false;
    document.getElementById('repetirPassword').required = false;
    
    // Mostrar y configurar los radio buttons de estado
    document.getElementById('divEstado').style.display = 'block';
    
    if(estado == '1'){
       document.getElementById("estadoActivo").checked = true;
    } else {
       document.getElementById("estadoInactivo").checked = true;
    }

    // Configurar el botón para edición
    let btnAgregarEditar = document.getElementById("btnAgregarEditar");
    btnAgregarEditar.setAttribute("value", "editar");
    btnAgregarEditar.value = "editar";
    btnAgregarEditar.innerHTML = "Actualizar";
    
    // Guardar el ID del usuario
    let idUsuaro = document.getElementById('idUsuaro');
    idUsuaro.value = id_usuario;
    
    modal.show();
  }

  
  function abrirModalAgregar() {
    // Cambiar título del modal
    document.getElementById('abrirModalAgregarLabel').innerHTML = 'Agregar Usuario';
    
    // Resetear formulario
    document.getElementById('formAgregarUsuario').reset();
    
    // Limpiar campos específicos
    document.getElementById('nuevoUsuario').value = '';
    document.getElementById('nuevoRol').value = '';
    document.getElementById('nuevoPassword').value = '';
    document.getElementById('repetirPassword').value = '';
    
    // Hacer los campos de contraseña obligatorios para nuevo usuario
    document.getElementById('nuevoPassword').required = true;
    document.getElementById('repetirPassword').required = true;
    
    // Ocultar campos de estado (no necesarios para nuevo usuario)
    document.getElementById('divEstado').style.display = 'none';
    
    // Configurar el botón para agregar
    let btnAgregarEditar = document.getElementById("btnAgregarEditar");
    btnAgregarEditar.setAttribute("value", "agregar");
    btnAgregarEditar.value = "agregar";
    btnAgregarEditar.innerHTML = "Agregar";
    
    // Limpiar ID de usuario
    document.getElementById('idUsuaro').value = '';
    
    modal.show();
  }

  function validarFormularioUsuario() {
    const pass = document.getElementById('nuevoPassword').value;
    const pass2 = document.getElementById('repetirPassword').value;
    const btnValue = document.getElementById('btnAgregarEditar').value;
    
    // Solo validar contraseñas si estamos agregando un usuario nuevo
    // o si se ha ingresado una nueva contraseña en modo edición
    if (btnValue === 'agregar' || (btnValue === 'editar' && pass.trim() !== '')) {
        const error = validarPassword(pass, pass2);
        if (error) {
            alert(error);
            return false;
        }
    }
    
    return true;
}
</script>
<script src="../js/validaciones.js"></script>

</body>
</html>
