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

function obtenerUsuarios($con) {
    $stmt = $con->prepare("
        SELECT 
            a.ID_USUARIO, 
            a.NOMBRE_USUARIO, 
            a.PASS_USUARIO, 
            b.NOMBRE_ROL,
            a.codigo_bodega
        FROM usuario AS a
        INNER JOIN rol_usuario AS b ON a.COD_ROL = b.COD_ROL
        WHERE a.ESTADO_USUARIO = 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }

    $usuarios = [];
    while ($fila = $result->fetch_assoc()) {
        $fila['CODIGO_BODEGA'] = $fila['codigo_bodega'];
        $usuarios[] = $fila;
    }

    $stmt->close();
    return $usuarios;
}
?>