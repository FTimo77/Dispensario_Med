<?php

    session_start();

 if (!isset($_SESSION['usuario']) &&  !isset($_SESSION['bodega'])) {
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
    $id_usuario =  $_GET['id_usuario'];
    eliminar_usuario($conexion, $id_usuario);
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nuevoUsuario'];
    $cod_rol = $_POST['nuevoRol'];
    $pass_usuario = $_POST['nuevoPassword'];
    $estado = $_POST['estado'];
    $agregarEditar=$_POST['agregarEditar'];
  //    echo $agregarEditar.'<br>';
  //   echo $nombre_usuario.'<br>';
  //    echo 'cod rol '.$cod_rol.'<br>';
  //  echo $pass_usuario.'<br>';
  //    echo"estado" .$estado.'<br>';
    if($agregarEditar=="agregar"){
        if (insert_usuario($conexion, $cod_rol, $nombre_usuario, $pass_usuario, $estado)) {
          header("Location: users.php");
          exit();
        } else {
            echo "<script>alert('Error al agregar el usuario');</script>";
        }
    }else if($agregarEditar=="editar"){
      $id_usuario=  $_POST['idUsuario'];
        if (editarUsuario($conexion, $id_usuario,$cod_rol, $nombre_usuario, $pass_usuario, $estado)) {
          header("Location: users.php");
          exit();
        } else {
            echo "<script>alert('Error al agregar el usuario');</script>";
        }
    }

}

function insert_usuario($conexion, $cod_rol, $nombre_usuario, $pass_usuario, $estado) {
    $stmt = $conexion->prepare("INSERT INTO usuario (COD_ROL, NOMBRE_USUARIO, PASS_USUARIO, ESTADO_USUARIO) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $cod_rol, $nombre_usuario, $pass_usuario, $estado);
    return $stmt->execute();
}

function editarUsuario($conexion, $id_usuario, $cod_rol, $nombre_usuario, $pass_usuario, $estado) {
    $stmt = $conexion->prepare("UPDATE usuario 
        SET COD_ROL = ?, 
            NOMBRE_USUARIO = ?, 
            PASS_USUARIO = ?, 
            ESTADO_USUARIO = ? 
        WHERE ID_USUARIO = ?");
        
    $stmt->bind_param("issss", $cod_rol, $nombre_usuario, $pass_usuario, $estado, $id_usuario);
    return $stmt->execute();
}

function eliminar_usuario($conexion, $id_usuario) {
    $stmt = $conexion->prepare("UPDATE usuario SET  ESTADO_USUARIO = '0' WHERE ID_USUARIO = ?");
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
            $usuarios = obtenerUsuarios($con,true);

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
                  . htmlspecialchars($usuario['PASS_USUARIO']) . "', '"
                  . htmlspecialchars($usuario['NOMBRE_ROL']) . "', '"
                  . htmlspecialchars($usuario['COD_ROL']) . "', '"
                  . htmlspecialchars($usuario['ESTADO_USUARIO']) . "')\">
                      <i class='bi bi-pencil-square'></i></a></td>";
                    echo "<td><a class='btn_eliminar' href='?id_usuario=" . htmlspecialchars($usuario['ID_USUARIO']) . "' onclick=\"return confirm('¿Estás seguro que deseas eliminar este usuario?')\"><i class='bi bi-trash3-fill'></i></a></td>";
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
        <input type="text" class="form-control" id="idUsuaro" name="idUsuario"  hidden readonly /><!-- id se habilita en la edision-->
      <label for="nuevoRol" class="form-label">Rol</label>
      <select class="form-select" id="nuevoRol" name="nuevoRol" required>
        <option id="rol" value="">Seleccione</option>
        <?php
          require_once "config/conexion.php";
          require_once "includes/usuario_model.php";

          $con = new Conexion();
          $con = $con->connect(); 
          $roles = obtenerRoles($con);
              if ($roles) {
                  foreach ($roles as $rol) {
                      echo '<option value="' . $rol['COD_ROL'] . '">' . htmlspecialchars($rol['NOMBRE_ROL']) . '</option>';
                  }
              } else {
                  echo "<postion > No  hay roles disponibles</option>";
              }
          ?>
      </select>
        </div>
        <div class="mb-3">
          <label for="nuevoUsuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="nuevoUsuario" name="nuevoUsuario" placeholder="Ingrese el nombre de usuario" required  oninput="soloLetras(this)"/>
        </div>
        <div class="mb-3">
          <label for="nuevoPassword" class="form-label">Contraseña</label>
          <input id="password" type="text" class="form-control" id="nuevoPassword" name="nuevoPassword" placeholder="Ingrese la contraseña" required  oninput="sinEspacios(this)" />
        </div>
        <div class="mb-3">
  <label class="form-label">Estado</label>
  <div>  <!-- -->
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="estado" id="estadoActivo" value="1" checked>
        <label class="form-check-label" for="estadoActivo" >Activo</label>
      </div>
      <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="estado" id="estadoInactivo" value="0">
        <label class="form-check-label" for="estadoInactivo">Inactivo</label>
    </div>
  </div>
</div>

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
  const modal = new bootstrap.Modal(document.getElementById('modalAgregarUsuario'));

  function abrirModalEditar(id_usuario, nombre,pass_usuario, rol, cod_rol,estado) {
    document.getElementById('nuevoUsuario').value = nombre;
    document.getElementById('rol').innerHTML = rol;
    document.getElementById('rol').value = cod_rol;
    document.getElementById('password').value = pass_usuario;
    
    
    let radio=null
    if(estado=='1'){
       radio =document.getElementById("estadoActivo")
        radio.checked=true
        radio.value='1'
    }else{
       radio =document.getElementById("estadoInactivo")
        radio.checked=true
        radio.value='0'
    }

    let btnAgregarEditar=document.getElementById("btnAgregarEditar")
    btnAgregarEditar.setAttribute("value","Actualizar")
    btnAgregarEditar.value="editar"
    btnAgregarEditar.innerHTML="Editar"
    // console.log(document.getElementById('nuevoUsuario'));
    // console.log(document.getElementById('rol'));
    // console.log(document.getElementById('password'));
    // console.log(radio);
    // console.log(btnAgregarEditar);
    
    let idUsuaro = document.getElementById('idUsuaro')
    idUsuaro.hidden=false
    idUsuaro.value=id_usuario
    console.log(idUsuaro);
    modal.show();
  }

  
  function abrirModalAgregar() {
    document.getElementById('formAgregarUsuario').reset();
    document.getElementById('nuevoUsuario').value = '';
    document.getElementById('rol').innerHTML = 'Seleccione';
    document.getElementById('rol').value = '';
     let btnAgregarEditar=document.getElementById("btnAgregarEditar")
    btnAgregarEditar.setAttribute("value","Actualizar")
    btnAgregarEditar.value="agregar"
    btnAgregarEditar.innerHTML="Agregar"
    console.log(btnAgregarEditar);
    modal.show();
  }
  

</script>

</body>
</html>
