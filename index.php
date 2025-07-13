<?php include __DIR__ . '/controllers/login_controller.php'; ?>
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


                <label class="formulario__label" for="bodega_seleccionada">Selecciona dispensario</label>
                <div class="input-group mb-3">
                    <select name="bodega_seleccionada" id="bodega_seleccionada" class="form-control" required>
                        <option value="">Seleccione:</option>
                        <?php
                        if (is_array($bodegas) && count($bodegas) > 0) {
                            foreach ($bodegas as $fila) {
                                echo "<option value='" . $fila['CODIGO_BODEGA'] . "'>" . $fila['DESCRIPCION'] . "</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No hay bodegas disponibles</option>";
                        }
                        ?>
                    </select>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                    // Recargar la página al cerrar el modal si se agregó una bodega
                    document.addEventListener('DOMContentLoaded', function () {
                        var form = document.getElementById('formNuevaBodega');
                        if (form) {
                            form.addEventListener('submit', function (e) {
                                e.preventDefault();
                                var nombre = document.getElementById('nueva_bodega').value.trim();
                                if (!nombre) return;
                                var formData = new FormData();
                                formData.append('nueva_bodega', nombre);
                                fetch('includes/procesar_bodega.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalBodega'));
                                            modal.hide();
                                            setTimeout(() => window.location.reload(), 400);
                                        } else {
                                            alert(data.message || 'Error al agregar la bodega');
                                        }
                                    })
                                    .catch(() => alert('Error al agregar la bodega'));
                            });
                        }
                    });
                </script>

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