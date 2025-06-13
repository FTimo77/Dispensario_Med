<?php
// includes/bodega_model.php
function obtenerBodegasActivas($conn) {
    $sql = "SELECT * FROM BODEGA WHERE ESTADO_BODEGA = '1'";
    return mysqli_query($conn, $sql);
}