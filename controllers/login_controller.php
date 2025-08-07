<?php
session_start();
require_once __DIR__ . '/../models/login_model.php';
$model = new LoginModel();
$mensaje = "";
$intento_login = false;
 
// Redirigir si ya está logueado
if (isset($_SESSION['usuario']) && isset($_SESSION['bodega'])) {
    header("Location: pages/menu_principal.php");
    exit;
}

// Mostrar mensaje de error
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $mensaje = "Usuario o clave incorrectos";
    $intento_login = true;
}

// Verificar si existen usuarios, si no, redirigir a primer ingreso
if ($model->usuariosCount() === 0) {
    header("Location: pages/primer_ingreso.php");
    exit;
}

// Procesar login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $intento_login = true;
    $usuario = trim($_POST['nombre_usuario'] ?? '');
    $clave = trim($_POST['pass_usuario'] ?? '');
    $bodega = $_POST['bodega_seleccionada'] ?? '';

    if ($usuario === "" || $clave === "" || $bodega === "") {
        $mensaje = "Por favor, complete todos los campos.";
    } else {
        $datos_usuario = $model->validarUsuario($usuario, $clave);
        if ($datos_usuario) {
            require_once __DIR__ . '/../includes/obtener_nom_bodega.php';
            $_SESSION['usuario'] = $usuario;
            $_SESSION['bodega'] = $bodega;
            $_SESSION['rol'] = $datos_usuario['rol'];
            $_SESSION['id_usuario'] = $datos_usuario['id'];
            obtenerNombreBodega($bodega);
            obtenerNombreRol($datos_usuario['rol']);
            header("Location: pages/menu_principal.php");
            $model->close();
            exit;
        } else {
            $_SESSION['usuario'] = null;
            $_SESSION['bodega'] = null;
            $model->close();
            header("Location: index.php?error=1");
            exit;
        }
    }
}

// Cargar bodegas para el select
$bodegas = $model->getBodegas();
$model->close();
