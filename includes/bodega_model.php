<?php
// includes/bodega_model.php
function obtenerBodegasActivas($conn) {
    $sql = "SELECT * FROM BODEGA WHERE ESTADO_BODEGA = 'A'";
    return mysqli_query($conn, $sql);
}