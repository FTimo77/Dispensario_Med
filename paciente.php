<?php
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once "./config/conexion.php";
require_once "./includes/usuario_model.php";

$conexion = new Conexion();
$conexion = $conexion->connect();

// Eliminar lógicamente al usuario
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    eliminar_usuario($conexion, $id_usuario);
    header("Location: paciente.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_p = $_POST['nombrep'];
    $apellido_p = $_POST['apellidop'];
    $empresa = isset($_POST['nuevaEmpresa']) && trim($_POST['nuevaEmpresa']) !== '' ? trim($_POST['nuevaEmpresa']) : $_POST['empresa'];
    $agregarEditar = $_POST['agregarEditar'];

    if ($agregarEditar == "agregar") {
        $estado = '1'; // Siempre activo al crear
        if (insert_usuario($conexion, $nombre_p, $apellido_p, $empresa, $estado)) {
            header("Location: paciente.php");
            exit();
        } else {
            echo "<script>alert('Error al agregar el paciente');</script>";
        }
    } elseif ($agregarEditar == "editar") {
        $id_usuario = $_POST['idUsuario'];
        $estado = '1'; // Siempre activo también al editar
        if (editarUsuario($conexion, $id_usuario, $nombre_p, $apellido_p, $empresa, $estado)) {
            header("Location: paciente.php");
            exit();
        } else {
            echo "<script>alert('Error al editar el paciente');</script>";
        }
    }
}

function insert_usuario($conexion, $nombre_p, $apellido_p, $empresa, $estado) {
    $stmt = $conexion->prepare("INSERT INTO pacientes (nombre_paciente, apellido_paciente , empresa, est_paciente) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre_p, $apellido_p, $empresa, $estado);
    return $stmt->execute();
}

function editarUsuario($conexion, $id_usuario, $nombre_p, $apellido_p, $empresa, $estado) {
    $stmt = $conexion->prepare("UPDATE pacientes 
        SET nombre_paciente = ?, 
            apellido_paciente = ?, 
            empresa = ?, 
            est_paciente = ? 
        WHERE id_paciente = ?");
    $stmt->bind_param("ssssi", $nombre_p, $apellido_p, $empresa, $estado, $id_usuario);
    return $stmt->execute();
}


function eliminar_usuario($conexion, $id_usuario) {
    $stmt = $conexion->prepare("UPDATE pacientes SET est_paciente = 0 WHERE id_paciente = ?");
    $stmt->bind_param("i", $id_usuario);
    return $stmt->execute();
}


function obtenerPacientes($conexion) {
    $query = "SELECT id_paciente, nombre_paciente, apellido_paciente, empresa, est_paciente FROM pacientes WHERE est_paciente = '1'";
    $resultado = $conexion->query($query);

    $pacientes = [];

    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $pacientes[] = $fila;
        }
    }

    return $pacientes;
}


?>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Pacientes</title>
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
    <h2 class="mb-0 px-3 py-2 rounded" style="background: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">Pacientes</h2>
    <button class="btn btn-primary" id="btnAbrirModal" onclick="abrirModalAgregar()">
     <i class="bi bi-person-plus"></i> Agregar Paciente
    </button>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre paciente</th>
              <th>Apellido paciente</th>
              <th>Empresa</th>
              <th>Estado</th>
              <th>Editar</th>
              <th>Eliminar</th>
            </tr>
          </thead>
          <tbody>
             <?php
            $pacientes = obtenerPacientes($conexion);
            if ($pacientes) {
                foreach ($pacientes as $p) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($p['id_paciente']) . "</td>";
                    echo "<td>" . htmlspecialchars($p['nombre_paciente']) . "</td>";
                    echo "<td>" . htmlspecialchars($p['apellido_paciente']) . "</td>";
                    echo "<td>" . htmlspecialchars($p['empresa']) . "</td>";
                    echo "<td>" . ($p['est_paciente'] == '1' ? 'Activo' : 'Inactivo') . "</td>";
                    echo "<td><a href='#' onclick=\"abrirModalEditar('{$p['id_paciente']}', '{$p['nombre_paciente']}', '{$p['apellido_paciente']}', '{$p['empresa']}', '{$p['est_paciente']}')\"><i class='bi bi-pencil-square text-success'></i></a></td>";
                    echo "<td><a href='?id_usuario=" . $p['id_paciente'] . "' onclick=\"return confirm('¿Estás seguro de eliminar este paciente?')\"><i class='bi bi-trash3-fill text-danger'></i></a></td>";
                    echo "</tr>";
                }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No hay pacientes registrados.</td></tr>";
                }
                ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  
</div>





<!-- Modal para agregar usuario -->
<div class="modal fade" id="modalAgregarpaciente" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formAgregarUsuario" autocomplete="off" method="POST" action="">
      <div class="modal-header">
        <h5 class="modal-title" id="abrirModalAgregar">Agregar Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
      <div class="mb-3">
          <label for="">Nombre de Paciente</label>  
          <input type="text" class="form-control uppercase-input" id="nombrep" name="nombrep" placeholder="Ingrese el nombre del paciente" required oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()"/>
      </div>
        <div class="mb-3">
          <label for="">Apellido de Paciente</label>  
          <input type="text" class="form-control uppercase-input" id="apellidop" name="apellidop" placeholder="Ingrese el apellido del paciente" required   required oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()"/>
      </div>
      <div class="mb-3">
        <label for="empresa">Empresa</label>
        <select class="form-select" id="empresa" name="empresa">
          <option value="" selected>Seleccione una empresa</option>
          <?php
            // Obtener empresas distintas de la tabla pacientes
            $empresas = [];
            $resultEmpresas = $conexion->query("SELECT DISTINCT empresa FROM pacientes WHERE empresa IS NOT NULL AND empresa != '' ORDER BY empresa ASC");
            if ($resultEmpresas && $resultEmpresas->num_rows > 0) {
              while ($row = $resultEmpresas->fetch_assoc()) {
                $emp = htmlspecialchars($row['empresa']);
                echo "<option value=\"$emp\">$emp</option>";
                $empresas[] = $emp;
              }
            }
          ?>
        </select>
        <div class="mt-2">
          <input type="text" class="form-control" id="nuevaEmpresa" name="nuevaEmpresa" placeholder="Agregar nueva empresa (opcional)" oninput="this.value = this.value.toUpperCase();">
        </div>
      </div>
        <!-- Estado oculto, siempre activo al crear y editar -->
        <input type="hidden" name="estado" value="1">
    </div>
    <div>
        <input type="hidden" id="idUsuaro" name="idUsuario" value="" hidden>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button  id="btnAgregarEditar" type="submit" class="btn btn-success" name="agregarEditar" value="agregar">Agregar</button>
      </div>
    </form>
  </div>
</div>



<script src="js/navbar-submenu.js"></script>
<script src="js/models.js"></script>
<script src="js/valitationInputs.js"></script><!--valida inputs -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  let modal;

  document.addEventListener('DOMContentLoaded', function () {
    // Inicializar el modal solo una vez al cargar
    modal = new bootstrap.Modal(document.getElementById('modalAgregarpaciente'));

    const btnAbrir = document.getElementById('btnAbrirModal');
    if (btnAbrir) {
      btnAbrir.addEventListener('click', function () {
        abrirModalAgregar();
      });
    }
  });

  function abrirModalAgregar() {
    document.getElementById('formAgregarUsuario').reset();
    document.getElementById('nombrep').value = '';
    document.getElementById('apellidop').value = '';
    document.getElementById('empresa').value = '';


    document.getElementById('btnAgregarEditar').value = 'agregar';
    document.getElementById('btnAgregarEditar').innerHTML = 'Agregar';

    const idUsuaro = document.getElementById('idUsuaro');
    if (idUsuaro) {
      idUsuaro.hidden = true;
      idUsuaro.value = '';
    }

    modal.show();
  }

  function abrirModalEditar(id, nombre, apellido, empresa, estado) {
  document.getElementById('nombrep').value = nombre;
  document.getElementById('apellidop').value = apellido;
  document.getElementById('empresa').value = empresa;



  document.getElementById('btnAgregarEditar').value = 'editar';
  document.getElementById('btnAgregarEditar').innerHTML = 'Editar';

  const idUsuaro = document.getElementById('idUsuaro');
  if (idUsuaro) {
    idUsuaro.hidden = false;
    idUsuaro.value = id;
  }

  modal.show();
}

</script>


</body>
</html>
