<?php
// Valida sesión
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once 'config/conexion.php';

$conexion = new Conexion();
$conn = $conexion->connect();

$lotes = [];
$mensaje = "";
$mensaje_acciones = "";

// Filtro de fechas
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$where_fecha = '';
if ($fecha_inicio && $fecha_fin) {
    $where_fecha = " AND l.FECHA_ING BETWEEN '" . $conn->real_escape_string($fecha_inicio) . "' AND '" . $conn->real_escape_string($fecha_fin) . "'";
}

// Tipo de reporte: 'general' o 'minimos'
$tipo = $_GET['tipo'] ?? 'general';

// Configuración según el tipo de reporte
if ($tipo === 'minimos') {
    $titulo = "Reporte Existencias mínimas";
    $extra_where = " AND l.CANTIDAD_LOTE = p.stock_min_prod";
    $mostrar_stock_actual = true;
    $mostrar_estado_lote = false;
    $mostrar_estado_prod = true;
} else {
    $titulo = "Reporte General de Lotes de Productos";
    $extra_where = "";
    $mostrar_stock_actual = true;
    $mostrar_estado_lote = true;
    $mostrar_estado_prod = false;
}

// Obtener productos para el filtro
$productos_filtro = [];
$res_prod = $conn->query("SELECT id_prooducto, NOM_PROD FROM producto WHERE estado_prod = 1 AND codigo_bodega = ".$_SESSION['bodega']);
while ($row = $res_prod->fetch_assoc()) {
    $productos_filtro[] = $row;
}
$producto_seleccionado = $_GET['producto'] ?? '';

$where_producto = '';
if ($producto_seleccionado) {
    $where_producto = " AND l.ID_PROODUCTO = " . intval($producto_seleccionado);
}

// Obtener categorías para el filtro
$categorias_filtro = [];
$res_cat = $conn->query("SELECT id_categoria, nombre_cat FROM categoria WHERE estado_cat = 1");
while ($row = $res_cat->fetch_assoc()) {
    $categorias_filtro[] = $row;
}
$categoria_seleccionada = $_GET['categoria'] ?? '';

$where_categoria = '';
if ($categoria_seleccionada) {
    $where_categoria = " AND p.ID_CATEGORIA = " . intval($categoria_seleccionada);
}

// Ordenar por stock
$orden_stock = $_GET['orden_stock'] ?? '';
$order_by_stock = '';
if ($orden_stock === 'asc') {
    $order_by_stock = 'l.CANTIDAD_LOTE ASC, ';
} elseif ($orden_stock === 'desc') {
    $order_by_stock = 'l.CANTIDAD_LOTE DESC, ';
}

// Consulta SQL
$sql_lotes = "
    SELECT
        l.NUM_LOTE, 
        l.CANTIDAD_LOTE,
        l.FECH_VENC, 
        l.FECH_FABRI, 
        l.FECHA_ING,
        l.ESTADO_LOTE,
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
        $extra_where
        $where_fecha
        $where_producto
        $where_categoria
    ORDER BY 
        $order_by_stock l.FECHA_ING DESC
";

$res_lotes = $conn->query($sql_lotes);

if ($res_lotes) {
    while ($row = $res_lotes->fetch_assoc()) {
        $lotes[] = $row;
    }
} else {
    $mensaje = "<div class='alert alert-danger text-center'>Error al cargar los lotes: " . $conn->error . "</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $titulo; ?></title>
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
                <?php echo $titulo; ?>
            </h2>
        </div>
        <!-- Filtro de fechas -->
        <form class="row g-3 mb-4" method="get">
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
            <div class="col-auto">
                <label for="producto" class="col-form-label">Producto:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="producto" name="producto">
                    <option value="">Todos</option>
                    <?php foreach ($productos_filtro as $prod): ?>
                        <option value="<?= $prod['id_prooducto'] ?>" <?= ($producto_seleccionado == $prod['id_prooducto']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod['NOM_PROD']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="categoria" class="col-form-label">Categoría:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias_filtro as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" <?= ($categoria_seleccionada == $cat['id_categoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre_cat']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="orden_stock" class="col-form-label">Ordenar por stock:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="orden_stock" name="orden_stock">
                    <option value="">Sin orden</option>
                    <option value="asc" <?= (($_GET['orden_stock'] ?? '') === 'asc') ? 'selected' : '' ?>>Menor a mayor</option>
                    <option value="desc" <?= (($_GET['orden_stock'] ?? '') === 'desc') ? 'selected' : '' ?>>Mayor a menor</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFechas">
                    <i class="bi bi-calendar-range"></i> Filtrar por fecha
                </button>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (!empty($mensaje_acciones)): ?>
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
                                <?php if ($mostrar_stock_actual): ?>
                                    <th>Stock Actual</th>
                                <?php endif; ?>
                                <th>Stock Mínimo</th>
                                <th>Fecha Fabricación</th>
                                <th>Fecha Vencimiento</th>
                                <th>Fecha Ingreso Lote</th>
                                <th>Bodega</th>
                                <?php if ($mostrar_estado_lote): ?>
                                    <th>Estado Lote</th>
                                <?php endif; ?>
                                <?php if ($mostrar_estado_prod): ?>
                                    <th>Estado Producto</th>
                                <?php endif; ?>
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
                                        <?php if ($mostrar_stock_actual): ?>
                                            <td><?php echo htmlspecialchars($lote['CANTIDAD_LOTE']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($lote['stock_min_prod']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['FECH_FABRI']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['FECH_VENC']); ?></td>
                                        <td><?php echo htmlspecialchars($lote['FECHA_ING']); ?></td>
                                        <td><?php echo htmlspecialchars($_SESSION['nombre_bodega']); ?></td>
                                        <?php if ($mostrar_estado_lote): ?>
                                            <td>
                                                <?php
                                                echo ($lote['ESTADO_LOTE'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                                ?>
                                            </td>
                                        <?php endif; ?>
                                        <?php if ($mostrar_estado_prod): ?>
                                            <td>
                                                <?php
                                                echo ($lote['estado_prod'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                                ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <!-- Dentro de <div class="card-body">, después de la tabla -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <button id="btnExportPDF" class="btn btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar a PDF
                        </button>
                        </div>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Filtro de Fechas -->
    <div class="modal fade" id="modalFechas" tabindex="-1" aria-labelledby="modalFechasLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="get">
          <div class="modal-header">
            <h5 class="modal-title" id="modalFechasLabel">Filtrar por rango de fechas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
            <input type="hidden" name="producto" value="<?php echo htmlspecialchars($producto_seleccionado); ?>">
            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria_seleccionada); ?>">
            <input type="hidden" name="orden_stock" value="<?php echo htmlspecialchars($orden_stock); ?>">
            <div class="mb-3">
              <label for="fecha_inicio" class="form-label">Desde:</label>
              <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            </div>
            <div class="mb-3">
              <label for="fecha_fin" class="form-label">Hasta:</label>
              <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Filtrar</button>
          </div>
        </form>
      </div>
    </div>

    <script src="js/models.js"></script>
    <script src="js/navbar-submenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Agrega esto ANTES del cierre de </body> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
  // Inicializa jsPDF
  const { jsPDF } = window.jspdf;
</script>
<script>
document.getElementById('btnExportPDF').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm'
    });

    // Título y fecha
    const title = "<?php echo $titulo; ?>";
    doc.setFontSize(16);
    doc.text(title, 15, 15);
    doc.setFontSize(10);
    doc.text(`Generado el: ${new Date().toLocaleDateString()}`, 15, 22);

    // Datos de la tabla desde PHP
    const headers = [
        "#",
        "Lote",
        "Producto",
        "Presentación",
        "Categoría"
        <?php if ($mostrar_stock_actual): ?>, "Stock Actual"<?php endif; ?>,
        "Stock Mínimo",
        "Fabricación",
        "Vencimiento",
        "Ingreso",
        "Bodega"
        <?php if ($mostrar_estado_lote): ?>, "Estado Lote"<?php endif; ?>
        <?php if ($mostrar_estado_prod): ?>, "Estado Producto"<?php endif; ?>
    ];

    const data = <?php echo json_encode($lotes); ?>.map((item, index) => [
        index + 1,
        item.NUM_LOTE,
        item.NOM_PROD,
        item.PRESENTACION_PROD,
        item.nombre_cat
        <?php if ($mostrar_stock_actual): ?>, item.CANTIDAD_LOTE<?php endif; ?>,
        item.stock_min_prod,
        item.FECH_FABRI,
        item.FECH_VENC,
        item.FECHA_ING,
        "<?php echo $_SESSION['nombre_bodega']; ?>"
        <?php if ($mostrar_estado_lote): ?>, item.ESTADO_LOTE === 1 ? "Activo" : "Inactivo"<?php endif; ?>
        <?php if ($mostrar_estado_prod): ?>, item.estado_prod === 1 ? "Activo" : "Inactivo"<?php endif; ?>
    ]);

    // Generar tabla
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 30,
        margin: { left: 10 },
        styles: {
            fontSize: 8,
            cellPadding: 1.5,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 8 },  // Columna #
            1: { cellWidth: 15 }, // Lote
            2: { cellWidth: 30 }, // Producto
            3: { cellWidth: 25 }, // Presentación
            4: { cellWidth: 25 }  // Categoría
            // ... ajusta según necesidad
        }
    });

    doc.save(`Reporte_${title.replace(/ /g, '_')}.pdf`);
});
</script>
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