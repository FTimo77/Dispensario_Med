<?php
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: ../../index.php");
    exit;
}

require_once '../../config/conexion.php';

$conexion = new Conexion();
$conn = $conexion->connect();

$movimientos = [];
$mensaje = "";

// Filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$producto_seleccionado = $_GET['producto'] ?? '';
$where_fecha = '';
$where_producto = '';

if ($fecha_inicio && $fecha_fin) {
    $where_fecha = " AND cab.FECHA_TRANSC BETWEEN '" . $conn->real_escape_string($fecha_inicio) . "' AND '" . $conn->real_escape_string($fecha_fin) . "'";
}

if ($producto_seleccionado) {
    $where_producto = " AND k.ID_PROODUCTO = " . intval($producto_seleccionado);
}

// Obtener productos para el filtro
$productos_filtro = [];
$res_prod = $conn->query("SELECT id_prooducto, NOM_PROD FROM producto WHERE estado_prod = 1 AND codigo_bodega = " . $_SESSION['bodega']);
while ($row = $res_prod->fetch_assoc()) {
    $productos_filtro[] = $row;
}

$tipo_movimiento = $_GET['tipo_movimiento'] ?? '';
$where_tipo = '';
if ($tipo_movimiento === 'I') {
    $where_tipo = " AND cab.TIPO_TRANSAC = 'I'";
} elseif ($tipo_movimiento === 'E') {
    $where_tipo = " AND cab.TIPO_TRANSAC = 'E'";
}

// Consulta principal
$sql_mov = "
    SELECT
        cab.TIPO_TRANSAC,
        k.ID_PROODUCTO,
        p.NOM_PROD,
        cab.FECHA_TRANSC,
        cab.MOTIVO,
        k.CANTIDAD
    FROM kardex k
    INNER JOIN cabecera cab ON k.COD_TRANSAC = cab.COD_TRANSAC
    INNER JOIN producto p ON k.ID_PROODUCTO = p.id_prooducto
    WHERE p.codigo_bodega = " . $_SESSION['bodega'] . "
        $where_fecha
        $where_producto
        $where_tipo
    ORDER BY cab.FECHA_TRANSC ASC
";

$res_mov = $conn->query($sql_mov);

if ($res_mov) {
    while ($row = $res_mov->fetch_assoc()) {
        $movimientos[] = $row;
    }
} else {
    $mensaje = "<div class='alert alert-danger text-center'>Error al cargar los movimientos: " . $conn->error . "</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte de Movimientos</title>
    <link rel="icon" href="../../assets/icons/capsule-pill.svg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <?php include '../../includes/navbar.php'; ?>
    <div class="container py-5 fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 px-3 py-2 rounded"
                style="background: rgba(255,255,255,0.85); box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                Reporte de Movimientos
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
                <label for="tipo_movimiento" class="col-form-label">Tipo:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="tipo_movimiento" name="tipo_movimiento">
                    <option value="">Todos</option>
                    <option value="I" <?= ($tipo_movimiento === 'I') ? 'selected' : '' ?>>Ingresos</option>
                    <option value="E" <?= ($tipo_movimiento === 'E') ? 'selected' : '' ?>>Egresos</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                    data-bs-target="#modalFechas">
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

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tipo de movimiento</th>
                                <th>Producto</th>
                                <th>Fecha de transacción</th>
                                <th>Motivo</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay movimientos para mostrar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $i => $mov): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td>
                                            <?php
                                            if ($mov['TIPO_TRANSAC'] === 'I')
                                                echo '<span class="badge bg-success">Ingreso</span>';
                                            elseif ($mov['TIPO_TRANSAC'] === 'E')
                                                echo '<span class="badge bg-danger">Egreso</span>';
                                            else
                                                echo htmlspecialchars($mov['TIPO_TRANSAC']);
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['NOM_PROD']); ?></td>
                                        <td><?php echo htmlspecialchars($mov['FECHA_TRANSC']); ?></td>
                                        <td><?php echo htmlspecialchars($mov['MOTIVO']); ?></td>
                                        <td><?php echo htmlspecialchars($mov['CANTIDAD']); ?></td>
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
                    <input type="hidden" name="producto"
                        value="<?php echo htmlspecialchars($producto_seleccionado); ?>">
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Desde:</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                            value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Hasta:</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                            value="<?php echo htmlspecialchars($fecha_fin); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../js/models.js"></script>
    <script src="../../js/navbar-submenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Boton exportar pdf -->
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
    const title = "Reporte de Movimientos";
    doc.setFontSize(16);
    doc.text(title, 15, 15);
    doc.setFontSize(10);
    doc.text(`Generado el: ${new Date().toLocaleDateString()}`, 15, 22);
    doc.text(`Bodega: <?php echo $_SESSION['nombre_bodega']; ?>`, 15, 28);
    
    // Filtros aplicados
    <?php if($fecha_inicio && $fecha_fin): ?>
        doc.text(`Período: ${"<?php echo $fecha_inicio; ?>"} al ${"<?php echo $fecha_fin; ?>"}`, 15, 34);
    <?php endif; ?>
    <?php if($tipo_movimiento): ?>
        doc.text(`Tipo: ${"<?php echo ($tipo_movimiento === 'I') ? 'Ingresos' : 'Egresos'; ?>"}`, 100, 34);
    <?php endif; ?>

    // Datos de la tabla desde PHP
    const headers = [
        "#",
        "Tipo",
        "Producto",
        "Fecha",
        "Motivo",
        "Cantidad"
    ];

    const data = <?php echo json_encode($movimientos); ?>.map((item, index) => [
        index + 1,
        item.TIPO_TRANSAC === 'I' ? 'Ingreso' : 'Egreso',
        item.NOM_PROD,
        item.FECHA_TRANSC,
        item.MOTIVO,
        item.CANTIDAD
    ]);

    // Generar tabla
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 40,
        margin: { left: 10 },
        styles: {
            fontSize: 8,
            cellPadding: 1.5,
            overflow: 'linebreak'
        },
        columnStyles: {
            0: { cellWidth: 8 },   // #
            1: { cellWidth: 15 },  // Tipo
            2: { cellWidth: 35 },  // Producto
            3: { cellWidth: 20 },  // Fecha
            4: { cellWidth: 40 },  // Motivo
            5: { cellWidth: 15 }   // Cantidad
        },
        didDrawCell: (data) => {
            // Colorear celdas de tipo
            if (data.column.index === 1) {
                const cellValue = data.cell.raw;
                if (cellValue === 'Ingreso') {
                    doc.setFillColor(200, 230, 200); // Verde claro
                } else if (cellValue === 'Egreso') {
                    doc.setFillColor(255, 200, 200); // Rojo claro
                }
                doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
                doc.setTextColor(0, 0, 0);
                doc.text(cellValue, data.cell.x + 2, data.cell.y + 5);
            }
        }
    });

    doc.save(`Reporte_Movimientos_${new Date().toISOString().slice(0,10)}.pdf`);
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