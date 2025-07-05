<?php
function validarUsuario($conn, $usuario, $clave) {
    $stmt = $conn->prepare("
        SELECT u.ID_USUARIO, u.NOMBRE_USUARIO, u.PASS_USUARIO, r.NOMBRE_ROL, u.COD_ROL
        FROM usuario u
        INNER JOIN rol_usuario r ON u.COD_ROL = r.COD_ROL
        WHERE u.NOMBRE_USUARIO = ? AND u.ESTADO_USUARIO = 1
        LIMIT 1
    ");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($clave, $row['PASS_USUARIO'])) {
            return [
                'id' => $row['ID_USUARIO'],
                'rol' => $row['COD_ROL'],
                'nombre_rol' => $row['NOMBRE_ROL']
            ];
        }
    }
    $stmt->close();
    return null;
}
function obtenerUsuarios($con, $traerInactivos=false) {
    if($traerInactivos){
        $stmt = $con->prepare("
            SELECT u.ID_USUARIO, u.NOMBRE_USUARIO, u.PASS_USUARIO, u.COD_ROL, u.ESTADO_USUARIO, r.NOMBRE_ROL
            FROM usuario u
            INNER JOIN rol_usuario r ON u.COD_ROL = r.COD_ROL
        ");
    } else {
        $stmt = $con->prepare("
            SELECT u.ID_USUARIO, u.NOMBRE_USUARIO, u.PASS_USUARIO, u.COD_ROL, u.ESTADO_USUARIO, r.NOMBRE_ROL
            FROM usuario u
            INNER JOIN rol_usuario r ON u.COD_ROL = r.COD_ROL
            WHERE u.ESTADO_USUARIO = 1
        ");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $stmt->close();
    return $usuarios;
}
function obtenerRoles($con){
    $stmt =$con->prepare("SELECT * FROM rol_usuario");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }

    $roles = [];
    while ($fila = $result->fetch_assoc()) {
        $roles[] = $fila;
    }

    $stmt->close();
    return $roles;
}
?>