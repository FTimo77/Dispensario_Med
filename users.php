<?php
require_once "./config/conexion.php";
require_once "./includes/usuario_model.php";

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$conexion = new Conexion();
$conexion = $conexion->connect();

// Eliminar lógicamente al usuario
if (isset($_GET['eliminar'])) {
    $id_usuario = intval($_GET['eliminar']);
    eliminar_usuario($conexion, $id_usuario);
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nuevoUsuario'];
    $cod_rol = $_POST['nuevoRol'];
    $pass_usuario = $_POST['nuevoPassword'];

    if (insert_usuario($conexion, $cod_rol, $nombre_usuario, $pass_usuario)) {
        header("Location: users.php");
        exit();
    } else {
        echo "<script>alert('Error al agregar el usuario');</script>";
    }
}

function insert_usuario($conexion, $cod_rol, $nombre_usuario, $pass_usuario) {
    $estado_default = '1'; // Activo por defecto
    $stmt = $conexion->prepare("INSERT INTO usuario (COD_ROL, NOMBRE_USUARIO, PASS_USUARIO, ESTADO_USUARIO) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $cod_rol, $nombre_usuario, $pass_usuario, $estado_default);
    return $stmt->execute();
}

function eliminar_usuario($conexion, $id_usuario) {
    $stmt = $conexion->prepare("UPDATE usuario SET ESTADO_USUARIO = '0' WHERE ID_USUARIO = ?");
    $stmt->bind_param("i", $id_usuario);
    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Usuarios</title>
  <link rel="icon" href="./assets/icons/capsule-pill.svg" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    .btn_editar { color: green; }
    .btn_eliminar { color: red; }
  </style>
</head>

<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
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
              <th>Password</th>
              <th>Estado</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>
          <tbody id="tablaUsuarios">
            <?php
            $con = new Conexion();
            $con = $con->connect();
            $usuarios = obtenerUsuarios($con);

            if ($usuarios) {
                foreach ($usuarios as $usuario) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($usuario['ID_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['NOMBRE_USUARIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['NOMBRE_ROL']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['PASS_USUARIO']) . "</td>";
                    echo "<td>" . ($usuario['ESTADO_USUARIO'] == '1' ? 'Activo' : 'Inactivo') . "</td>";
                    echo "<td><a class='btn_editar' href='#' onclick=\"abrirModalEditar('"
                      . htmlspecialchars($usuario['ID_USUARIO']) . "', '"
                      . htmlspecialchars($usuario['NOMBRE_USUARIO']) . "', '"
                      . htmlspecialchars($usuario['NOMBRE_ROL']) . "')\">
                      <i class='bi bi-pencil-square'></i></a></td>";
                    echo "<td><a class='btn_eliminar' href='?eliminar=" . htmlspecialchars($usuario['ID_USUARIO']) . "' onclick=\"return confirm('¿Estás seguro que deseas eliminar este usuario?')\"><i class='bi bi-trash3-fill'></i></a></td>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No hay datos</td></tr>";
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
    <form class="modal-content" id="formAgregarUsuario" autocomplete="off" method="POST" action="">
      <div class="modal-header">
        <h5 class="modal-title" id="abrirModalAgregarLabel">Agregar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
      <label for="nuevoRol" class="form-label">Rol</label>
      <select class="form-select" id="nuevoRol" name="nuevoRol" required>
        <?php
          require_once "config/conexion.php";
          require_once "includes/usuario_model.php";

          $con = new Conexion();
          $con = $con->connect(); 
          $roles = obtenerRoles($con);
              if ($roles) {
                  foreach ($roles as $rol) {
                      echo "<option>" . htmlspecialchars($rol['NOMBRE_ROL']) . "</option>";
                  }
              } else {
                  echo "<postion > No  hay roles disponibles</option>";
              }
          ?>
      </select>
        </div>
        <div class="mb-3">
          <label for="nuevoUsuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="nuevoUsuario" name="nuevoUsuario" placeholder="Ingrese el nombre de usuario" required />
        </div>
        <div class="mb-3">
          <label for="nuevoPassword" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="nuevoPassword" name="nuevoPassword" placeholder="Ingrese la contraseña" required />
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
  const modal = new bootstrap.Modal(document.getElementById('modalAgregarUsuario'));

  function abrirModalAgregar() {
    document.getElementById('formAgregarUsuario').reset();
    document.getElementById('nuevoUsuario').value = '';
    document.getElementById('nuevoRol').value = '';
    modal.show();
  }

  function abrirModalEditar(id, nombre, rol) {
    document.getElementById('nuevoUsuario').value = nombre;
    document.getElementById('nuevoRol').value = rol;
    modal.show();
  }
</script>

</body>
</html>
