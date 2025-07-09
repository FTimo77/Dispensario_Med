<?php
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once 'config/conexion.php';

$conexion = new Conexion();
$conn = $conexion->connect();

$mensaje = "";

// Acción de dar de baja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dar_baja_lote'])) {
    $num_lote_baja = $_POST['dar_baja_lote'];

    // 1. Obtener cantidad y producto del lote
    $stmt_info = $conn->prepare("SELECT CANTIDAD_LOTE, ID_PROODUCTO FROM lote WHERE NUM_LOTE = ?");
    $stmt_info->bind_param("s", $num_lote_baja);
    $stmt_info->execute();
    $stmt_info->bind_result($cantidad_lote, $id_producto);
    if ($stmt_info->fetch()) {
        $stmt_info->close();

        // 2. Restar la cantidad al stock del producto
        $stmt_update_prod = $conn->prepare("UPDATE producto SET stock_act_prod = stock_act_prod - ? WHERE id_prooducto = ?");
        $stmt_update_prod->bind_param("ii", $cantidad_lote, $id_producto);
        $stmt_update_prod->execute();
        $stmt_update_prod->close();

        // 3. Dar de baja el lote
        $stmt_baja = $conn->prepare("UPDATE lote SET ESTADO_LOTE = 0 WHERE NUM_LOTE = ?");
        $stmt_baja->bind_param("s", $num_lote_baja);
        if ($stmt_baja->execute()) {
            $mensaje = "<div class='alert alert-success text-center'>Lote $num_lote_baja dado de baja correctamente y stock actualizado.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger text-center'>Error al dar de baja el lote: " . $stmt_baja->error . "</div>";
        }
        $stmt_baja->close();
    } else {
        $mensaje = "<div class='alert alert-danger text-center'>No se pudo obtener la información del lote.</div>";
        $stmt_info->close();
    }
}

$fecha_hoy = date('Y-m-d');
$producto_seleccionado = $_GET['producto'] ?? '';
$categoria_seleccionada = $_GET['categoria'] ?? '';

$where_producto = '';
$where_categoria = '';

if ($producto_seleccionado) {
    $where_producto = " AND l.ID_PROODUCTO = " . intval($producto_seleccionado);
}
if ($categoria_seleccionada) {
    $where_categoria = " AND p.ID_CATEGORIA = " . intval($categoria_seleccionada);
}

// Obtener productos para el filtro
$productos_filtro = [];
$res_prod = $conn->query("SELECT id_prooducto, NOM_PROD FROM producto WHERE estado_prod = 1 AND codigo_bodega = " . $_SESSION['bodega']);
while ($row = $res_prod->fetch_assoc()) {
    $productos_filtro[] = $row;
}

// Obtener categorías para el filtro
$categorias_filtro = [];
$res_cat = $conn->query("SELECT id_categoria, nombre_cat FROM categoria WHERE estado_cat = 1");
while ($row = $res_cat->fetch_assoc()) {
    $categorias_filtro[] = $row;
}

// Consulta principal: lotes caducados
$sql = "
    SELECT
        l.NUM_LOTE,
        l.CANTIDAD_LOTE,
        l.FECH_VENC,
        l.FECHA_ING,
        l.ESTADO_LOTE,
        p.NOM_PROD,
        p.PRESENTACION_PROD,
        c.nombre_cat
    FROM lote l
    INNER JOIN producto p ON l.ID_PROODUCTO = p.id_prooducto
    INNER JOIN categoria c ON p.ID_CATEGORIA = c.id_categoria
    WHERE p.estado_prod = 1
      AND p.codigo_bodega = " . $_SESSION['bodega'] . "
      AND l.FECH_VENC < '$fecha_hoy'
      $where_producto
      $where_categoria
    ORDER BY l.FECH_VENC DESC
";

$res = $conn->query($sql);
$lotes = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $lotes[] = $row;
    }
} else {
    $mensaje .= "<div class='alert alert-danger text-center'>Error al cargar los lotes: " . $conn->error . "</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte de Productos Caducados</title>
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
                Reporte de Productos Caducados
            </h2>
        </div>
        <!-- Filtros -->
        <form class="row g-3 mb-4" method="get">
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
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
        <?php if (!empty($mensaje)): ?>
            <?php echo $mensaje; // El mensaje ya incluye la clase alert-success o alert-danger ?>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <form method="post">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Lote</th>
                                    <th>Producto</th>
                                    <th>Presentación</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Estado Lote</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lotes)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No hay productos caducados para mostrar.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lotes as $i => $lote): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($lote['NUM_LOTE']); ?></td>
                                            <td><?php echo htmlspecialchars($lote['NOM_PROD']); ?></td>
                                            <td><?php echo htmlspecialchars($lote['PRESENTACION_PROD']); ?></td>
                                            <td><?php echo htmlspecialchars($lote['nombre_cat']); ?></td>
                                            <td><?php echo htmlspecialchars($lote['CANTIDAD_LOTE']); ?></td>
                                            <td class="text-danger fw-bold"><?php echo htmlspecialchars($lote['FECH_VENC']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($lote['FECHA_ING']); ?></td>
                                            <td>
                                                <?php
                                                echo ($lote['ESTADO_LOTE'] == 1)
                                                    ? '<span class="badge bg-success">Activo</span>'
                                                    : '<span class="badge bg-danger">Inactivo</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($lote['ESTADO_LOTE'] == 1 && $lote['CANTIDAD_LOTE'] > 0): ?>
                                                    <button type="submit" name="dar_baja_lote"
                                                        value="<?= htmlspecialchars($lote['NUM_LOTE']) ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Está seguro de dar de baja este lote?');">
                                                        Dar de baja
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
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
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="js/models.js"></script>
    <script src="js/navbar-submenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- aqui añadimos el js para la funcionalidad de exportar en pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
  const { jsPDF } = window.jspdf;
</script>
<script>
document.getElementById('btnExportPDF').addEventListener('click', function() {
    // Configuración del PDF
    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm'
    });

    // Título y fecha
    const title = "Reporte de Productos Caducados";
    doc.setFontSize(16);
    doc.text(title, 15, 15);
    doc.setFontSize(10);
    doc.text(`Generado el: ${new Date().toLocaleDateString()}`, 15, 22);
    doc.text(`Bodega: <?php echo $_SESSION['nombre_bodega']; ?>`, 15, 28);

    // Datos de la tabla desde PHP
    const headers = [
        "#",
        "Lote",
        "Producto",
        "Presentación",
        "Categoría",
        "Cantidad",
        "Fecha Vencimiento",
        "Fecha Ingreso",
        "Estado Lote"
    ];

    const data = <?php echo json_encode($lotes); ?>.map((item, index) => [
        index + 1,
        item.NUM_LOTE,
        item.NOM_PROD,
        item.PRESENTACION_PROD,
        item.nombre_cat,
        item.CANTIDAD_LOTE,
        item.FECH_VENC,
        item.FECHA_ING,
        item.ESTADO_LOTE === 1 ? "Activo" : "Inactivo"
    ]);

    // Generar tabla
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 35,
        margin: { left: 10 },
        styles: {
            fontSize: 8,
            cellPadding: 1.5,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 8 },   // #
            1: { cellWidth: 15 },  // Lote
            2: { cellWidth: 30 },  // Producto
            3: { cellWidth: 25 },  // Presentación
            4: { cellWidth: 25 },  // Categoría
            5: { cellWidth: 15 },  // Cantidad
            6: { cellWidth: 20 },  // Fecha Vencimiento
            7: { cellWidth: 20 },  // Fecha Ingreso
            8: { cellWidth: 15 }   // Estado
        },
        didDrawCell: (data) => {
            // Resaltar fechas vencidas
            if (data.column.index === 6) {
                const cellValue = data.cell.raw;
                if (new Date(cellValue) < new Date()) {
                    doc.setTextColor(255, 0, 0); // Rojo para vencidos
                    doc.text(cellValue, data.cell.x + 2, data.cell.y + 5);
                    doc.setTextColor(0, 0, 0); // Restaurar color
                }
            }
        }
    });

    doc.save(`Reporte_Productos_Caducados_<?php echo $_SESSION['nombre_bodega']; ?>_${new Date().toISOString().slice(0,10)}.pdf`);
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