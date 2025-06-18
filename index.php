<?php
session_start();
$mensaje = "";
$intento_login = false;

// Mostrar mensaje si viene de la redirección con error
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $mensaje = "Usuario o clave incorrectos";
    $intento_login = true;
}

// Procesamiento del login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $intento_login = true;
    require_once 'config/conexion.php';
    require_once 'includes/usuario_model.php';

    $usuario = trim($_POST['nombre_usuario']);
    $clave = trim($_POST['pass_usuario']);
    $bodega = $_POST['bodega_seleccionada'];

    if ($usuario === "" || $clave === "" || $bodega === "") {
        $mensaje = "Por favor, complete todos los campos.";
    } else {
        $conexion = new Conexion();
        $conn = $conexion->connect();

        // NOTA: La función validarUsuario() debe ser modificada para que devuelva un array con el rol y el ID.
        // Ejemplo de retorno: ['rol' => 'admin', 'id' => 1]
        $datos_usuario = validarUsuario($conn, $usuario, $clave);

        if ($datos_usuario) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['bodega'] = $bodega;
            $_SESSION['rol'] = $datos_usuario['rol'];
            $_SESSION['id_usuario'] = $datos_usuario['id']; // <-- ID del usuario agregado a la sesión
            header("Location: menu_principal.php");
            mysqli_close($conn);
            exit;
        } else {
            $_SESSION['usuario'] = null;
            $_SESSION['bodega'] = null;
            // Redirigir a la página de login con el mensaje
            mysqli_close($conn);
            header("Location: index.php?error=1");
            exit;
        }
    }
}
// Si el usuario ya está logueado, redirigir al menú principal
//if (isset($_SESSION['usuario']) && isset($_SESSION['bodega'])) {
//  header("Location: menu_principal.html");
//exit;
//}

// Cargar bodegas para el select (solo una vez)
require_once 'config/conexion.php';
require_once 'includes/bodega_model.php';
$conexion = new Conexion();
$conn = $conexion->connect();
$bodegas = obtenerBodegasActivas($conn);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center min-vh-100 fade-in">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <form method="POST" action="" class="formulario">
                <h3 class="formulario__titulo text-center mb-3">Ingresar al sistema</h3>
                <label class="formulario__label" for="nombre_usuario">Nombre usuario</label>
                <input class="formulario_text form-control mb-3" id="nombre_usuario" type="text" name="nombre_usuario"
                    placeholder="Ingrese Usuario" required>

                <label class="formulario__label" for="pass_usuario">Clave usuario</label>
                <input class="formulario_text form-control mb-3" id="pass_usuario" type="password" name="pass_usuario"
                    placeholder="Ingrese clave" required>

                <label class="formulario__label" for="seleccionar_dispensario">Selecciona dispensario</label>
                <select name="bodega_seleccionada" class="form-control mb-3" required>
                    <option value="">Seleccione:</option>
                    <?php
                    if (!$conn) {
                        echo "<option value='' disabled>Error al conectar</option>";
                    } elseif (!$bodegas) {
                        echo "<option value='' disabled>Error al ejecutar la consulta</option>";
                    } elseif (mysqli_num_rows($bodegas) > 0) {
                        while ($fila = mysqli_fetch_assoc($bodegas)) {
                            echo "<option value='" . $fila['CODIGO_BODEGA'] . "'>" . $fila['DESCRIPCION'] . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No hay bodegas disponibles</option>";
                    }
                    mysqli_close($conn);
                    ?>
                </select>

                <div class="d-grid">
                    <input class="formulario__btn btn btn-primary" type="submit" name="login" value="Entrar">
                </div>

                <?php if ($intento_login && !empty($mensaje)): ?>
                    <div class="alert alert-danger mt-3 py-2 text-center" role="alert">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>