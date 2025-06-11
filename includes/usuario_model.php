<?php
function validarUsuario($conn, $usuario, $clave) {
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE nombre_usuario = ? AND pass_usuario = ? AND ESTADO_USUARIO = 'A'");
    $stmt->bind_param("ss", $usuario, $clave);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user["COD_ROL"]; // O cualquier otro campo que necesites
    }

    $stmt->close();
    return null;
}

?>