<?php
require_once 'config/conexion.php';

$conexion = new Conexion();
$conn = $conexion->connect();
$mensaje = "";

// Procesar registro inicial
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["crear_inicial"])) {
    $nuevo_usuario = trim($_POST['nuevo_usuario']);
    $nueva_clave = trim($_POST['nueva_clave']);
    $nombre_bodega = trim($_POST['nombre_bodega']);

    if ($nuevo_usuario === "" || $nueva_clave === "" || $nombre_bodega === "") {
        $mensaje = "Por favor, complete todos los campos.";
    } else {
        // Crear bodega
        $stmt_bodega = $conn->prepare("INSERT INTO bodega (DESCRIPCION, estado_bodega) VALUES (?, '1')");
        $stmt_bodega->bind_param("s", $nombre_bodega);
        if ($stmt_bodega->execute()) {
            $codigo_bodega = $conn->insert_id;
            $stmt_bodega->close();

            // Crear usuario (rol admin por defecto)
            $hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
            $stmt_usuario = $conn->prepare("INSERT INTO usuario (nombre_usuario, pass_usuario, cod_rol, estado_usuario) VALUES (?, ?, '1', '1')");
            $stmt_usuario->bind_param("ss", $nuevo_usuario, $hash);
            if ($stmt_usuario->execute()) {
                $mensaje = "Usuario y bodega creados correctamente. Ahora puede iniciar sesión.";
                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Error al crear el usuario: " . $stmt_usuario->error;
            }
            $stmt_usuario->close();
        } else {
            $mensaje = "Error al crear la bodega: " . $stmt_bodega->error;
            $stmt_bodega->close();
        }
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
    <link rel="stylesheet" href="css/style.css">
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
    <script src="js/validaciones.js"></script>
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