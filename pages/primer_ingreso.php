
<?php
require_once '../config/conexion.php';
require_once '../includes/usuario_manager.php';

$conexion = new Conexion();
$conn = $conexion->connect();
$mensaje = "";

// Procesar registro inicial usando la clase
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["crear_inicial"])) {
    $usuarioManager = new UsuarioManager($conn);
    $exito = $usuarioManager->registrarConBodega($_POST['nuevo_usuario'], $_POST['nueva_clave'], $_POST['nombre_bodega']);
    $mensaje = $usuarioManager->mensaje;
    if ($exito) {
        header("Location: ../index.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Primer ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100 fade-in">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <form method="POST" action="" onsubmit="return validarFormularioUsuario()">
                <h3 class="text-center mb-3">Creación de Usuario Administrador</h3>
                <label for="nuevo_usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control mb-3" id="nuevo_usuario" name="nuevo_usuario" required>

                <label for="nueva_clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control mb-3" id="nuevoPassword" name="nueva_clave" required>

                <label for="repetirPassword" class="form-label">Repetir Contraseña</label>
                <input type="password" class="form-control mb-3" id="repetirPassword" name="repetirPassword" required>

                <label for="nombre_bodega" class="form-label">Nombre de la bodega Principal</label>
                <input type="text" class="form-control mb-3" id="nombre_bodega" name="nombre_bodega" required>

                <div class="d-grid">
                    <input class="btn btn-primary" type="submit" name="crear_inicial" value="Crear">
                </div>
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-danger mt-3 py-2 text-center" role="alert">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/validaciones.js"></script>
    <script>
        function validarFormularioUsuario() {
            const pass = document.getElementById('nuevoPassword').value;
            const pass2 = document.getElementById('repetirPassword').value;
            const error = validarPassword(pass, pass2); // Esta sí está en js/validaciones.js
            if (error) {
                alert(error);
                return false;
            }
            return true;
        }
    </script>
</body>
</html>