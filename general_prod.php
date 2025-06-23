<?php
// Valida sesión
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once 'config/conexion.php';
// El archivo producto_model.php no es estrictamente necesario aquí si solo vamos a consultar lotes directamente,
// pero lo mantengo por si tus funciones de utilidad para productos aún se usan en otras partes de tu app.
// require_once 'includes/producto_model.php'; 

// Conexión
$conexion = new Conexion();
$conn = $conexion->connect();

$lotes = []; // Inicializamos la variable lotes

// Cargar los lotes con la información del producto y la categoría
// He asumido que 'lote' tiene un ID de producto, y 'producto' tiene un ID de categoría.
// También he asumido que el 'id_producto' en 'lote' es el mismo que 'id_prooducto' en 'producto'.
// Y que el 'id_categoria' en 'producto' es el mismo que 'id_categoria' en 'categoria'.
// Asegúrate de que los nombres de tus columnas y tablas son correctos.
$sql_lotes = "
    SELECT
        l.NUM_LOTE, 
        l.FECH_VENC, 
        l.FECH_FABRI, 
        l.FECHA_ING,
        p.NOM_PROD, 
        p.PRESENTACION_PROD, 
        p.stock_min_prod,
        p.stock_act_prod,
        p.estado_prod,
        p.CODIGO_BODEGA,
        c.nombre_cat
    FROM 
        lote l 
    INNER JOIN 
        producto p ON l.ID_PROODUCTO = p.id_prooducto
    INNER JOIN
        categoria c ON p.ID_CATEGORIA = c.id_categoria
    WHERE 
        p.estado_prod = 1 
        AND p.CODIGO_BODEGA = ".$_SESSION['bodega']."
    ORDER BY 
        l.FECHA_ING DESC
";

$res_lotes = $conn->query($sql_lotes); // Esta es la línea 48 (o la que se ajuste después de mis cambios de formato)

if ($res_lotes) {
    while ($row = $res_lotes->fetch_assoc()) {
        $lotes[] = $row;
    }
} else {
    // Si hay un error en la consulta, puedes manejarlo aquí
    $mensaje = "<div class='alert alert-danger text-center'>Error al cargar los lotes: " . $conn->error . "</div>";
}

// Mensaje de éxito o error (para acciones futuras si se reincorporan)
$mensaje_acciones = "";

// Bloques comentados de eliminar producto/categoría:
// Los dejo comentados ya que este archivo está enfocado en reportes.
// Si necesitas estas funcionalidades, deberían ir en un archivo dedicado a la gestión de productos/categorías.

// Eliminando toda la lógica de POST de creación de producto/categoría
// ya que este archivo es para reporte/visualización de lotes.

// Cargar productos con su categoría usando el modelo (si se requiere para otra cosa)
// $productos = obtenerProductos($conn); // Esta línea se usaba para la tabla de productos, no de lotes.

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte de Lotes</title>
    <link rel="icon" href="./assets/icons/capsule-pill.svg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    <div class="container py-5 fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 px-3 py-2 rounded"
                style="background: rgba(255,255,255,0.85); box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                Reporte General de Lotes de Productos
            </h2>
        </div>
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (!empty($mensaje_acciones)): // Para mensajes de eliminar, si los reincorporas ?>
            <div class="alert alert-info text-center"><?php echo $mensaje_acciones; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Presentación</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Fecha Fabricación</th>
                                <th>Fecha Vencimiento</th>
                                <th>Fecha Ingreso Lote</th>
                                <th>Bodega</th>
                                <th>Estado Producto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lotes)): ?>
                                <tr>
                                    <td colspan="12" class="text-center">No hay lotes registrados para mostrar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lotes as $i => $lote): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($lote['NUM_LOTE']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['NOM_PROD']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['PRESENTACION_PROD']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['nombre_cat']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['stock_act_prod']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['stock_min_prod']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['FECH_FABRI']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['FECH_VENC']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['FECHA_ING']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['CODIGO_BODEGA']); ?></td>
                                        <td>
                                            <?php
                                            echo ($lote['estado_prod'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="js/models.js"></script>
    <script src="js/navbar-submenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <div class="wave-container">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"
            style="display:block; width:100vw; height:auto; margin:0; padding:0;">
            <path fill="#0099ff" fill-opacity="1" d="M0,256L48,261.3C96,267,192,277,288,240C384,203,480,117,576,101.3C672,85,
                768,139,864,144C960,149,1056,107,1152,85.3C1248,64,1344,64,1392,64L1440,64L1440,
                320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,
                576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</body>

</html>