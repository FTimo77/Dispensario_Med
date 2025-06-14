<?php
function validarUsuario($conn, $usuario, $clave) {
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE nombre_usuario = ? AND pass_usuario = ? AND ESTADO_USUARIO = '1'");
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
function obtenerUsuarios($con) {
    $stmt = $con->prepare("
        SELECT 
            u.ID_USUARIO, 
            u.NOMBRE_USUARIO, 
            u.PASS_USUARIO, 
            u.ESTADO_USUARIO,
            r.NOMBRE_ROL
        FROM usuario AS u
        INNER JOIN rol_usuario AS r ON u.COD_ROL = r.COD_ROL
        WHERE u.ESTADO_USUARIO = 1
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }

    $usuarios = [];
    while ($fila = $result->fetch_assoc()) {
        $usuarios[] = $fila;
    }

    $stmt->close();
    return $usuarios;
}

?>