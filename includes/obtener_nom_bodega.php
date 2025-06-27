<?php
function obtenerNombreBodega($bodega) {
    // Obtener el nombre de la bodega seleccionada
    require_once 'config/conexion.php';
    $conexion2 = new Conexion();
    $conn2 = $conexion2->connect();
    $stmt_bod = $conn2->prepare("SELECT DESCRIPCION FROM bodega WHERE CODIGO_BODEGA = ?");
    $stmt_bod->bind_param("s", $bodega);
    $stmt_bod->execute();
    $stmt_bod->bind_result($nombre_bodega);
    $stmt_bod->fetch();
    $stmt_bod->close();
    $conn2->close();

    $_SESSION['nombre_bodega'] = $nombre_bodega ?: '';
}

function obtenerNombreRol($rol) {
    // Obtener el nombre del rol del usuario
    require_once 'config/conexion.php';
    $conexion = new Conexion();
    $conn = $conexion->connect();
    $stmt_bod = $conn->prepare('SELECT NOMBRE_ROL FROM rol_usuario WHERE COD_ROL = ?');
    $stmt_bod->bind_param('s', $rol);
    $stmt_bod->execute();
    $stmt_bod->bind_result($nombre_rol);
    $stmt_bod->fetch();
    $stmt_bod->close();
    $conn->close();

    $_SESSION['nombre_rol'] = $nombre_rol ?: '';
}