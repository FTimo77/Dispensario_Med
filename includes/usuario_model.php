<?php
function validarUsuario($conn, $usuario, $clave) {
    // Prepara la consulta para evitar inyección SQL
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE nombre_usuario = ? AND pass_usuario = ? AND ESTADO_USUARIO = 'A'");
    $stmt->bind_param("ss", $usuario, $clave);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si encuentra al menos un usuario, es válido
    $esValido = ($result->num_rows > 0);

    $stmt->close();
    return $esValido;
}
?>