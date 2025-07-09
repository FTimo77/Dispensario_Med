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

$recetas = [];
$mensaje = "";

// Filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$paciente_seleccionado = $_GET['paciente'] ?? '';
$producto_seleccionado = $_GET['producto'] ?? '';
$empresa_seleccionada = $_GET['empresa'] ?? '';

$where_fecha = '';
$where_paciente = '';
$where_producto = '';
$where_empresa = '';

// Filtro por fecha
if ($fecha_inicio && $fecha_fin) {
    $where_fecha = " AND cab.FECHA_TRANSC BETWEEN '" . $conn->real_escape_string($fecha_inicio) . "' AND '" . $conn->real_escape_string($fecha_fin) . "'";
}

// Filtro por paciente
if ($paciente_seleccionado) {
    $where_paciente = " AND cab.id_paciente = " . intval($paciente_seleccionado);
}

// Filtro por producto
if ($producto_seleccionado) {
    $where_producto = " AND k.ID_PROODUCTO = " . intval($producto_seleccionado);
}

// Filtro por empresa
if ($empresa_seleccionada) {
    $where_empresa = " AND pac.empresa = '" . $conn->real_escape_string($empresa_seleccionada) . "'";
}

// Obtener empresas para el filtro
$empresas_filtro = [];
$res_emp = $conn->query("SELECT DISTINCT empresa FROM pacientes WHERE empresa IS NOT NULL AND empresa <> ''");
while ($row = $res_emp->fetch_assoc()) {
    $empresas_filtro[] = $row['empresa'];
}

// Obtener pacientes para el filtro (según empresa si aplica)
$pacientes_filtro = [];
$sql_pac = "SELECT id_paciente, nombre_paciente, apellido_paciente FROM pacientes WHERE est_paciente = 1";
if ($empresa_seleccionada) {
    $sql_pac .= " AND empresa = '" . $conn->real_escape_string($empresa_seleccionada) . "'";
}
$res_pac = $conn->query($sql_pac);
while ($row = $res_pac->fetch_assoc()) {
    $pacientes_filtro[] = $row;
}

// Obtener productos para el filtro
$productos_filtro = [];
$res_prod = $conn->query("SELECT id_prooducto, NOM_PROD FROM producto WHERE estado_prod = 1 AND codigo_bodega = ".$_SESSION['bodega']);
while ($row = $res_prod->fetch_assoc()) {
    $productos_filtro[] = $row;
}

// Consulta principal
$sql_recetas = "
    SELECT
        cab.FECHA_TRANSC,
        cab.MOTIVO,
        pac.nombre_paciente,
        pac.apellido_paciente,
        pac.empresa,
        k.CANTIDAD,
        p.NOM_PROD
    FROM kardex k
    INNER JOIN cabecera cab ON k.COD_TRANSAC = cab.COD_TRANSAC
    INNER JOIN pacientes pac ON cab.id_paciente = pac.id_paciente
    INNER JOIN producto p ON k.ID_PROODUCTO = p.id_prooducto
    WHERE cab.id_paciente IS NOT NULL
        AND p.codigo_bodega = ".$_SESSION['bodega']."
        $where_fecha
        $where_paciente
        $where_producto
        $where_empresa
    ORDER BY cab.FECHA_TRANSC DESC
";

$res_recetas = $conn->query($sql_recetas);

if ($res_recetas) {
    while ($row = $res_recetas->fetch_assoc()) {
        $recetas[] = $row;
    }
} else {
    $mensaje = "<div class='alert alert-danger text-center'>Error al cargar las recetas: " . $conn->error . "</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte de Recetas Emitidas</title>
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
                Reporte de Recetas Emitidas
            </h2>
        </div>
        <!-- Filtros -->
        <form class="row g-3 mb-4" method="get">
            <div class="col-auto">
                <label for="paciente" class="col-form-label">Paciente:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="paciente" name="paciente">
                    <option value="">Todos</option>
                    <?php foreach ($pacientes_filtro as $pac): ?>
                        <option value="<?= $pac['id_paciente'] ?>" <?= ($paciente_seleccionado == $pac['id_paciente']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pac['nombre_paciente'] . ' ' . $pac['apellido_paciente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                <label for="empresa" class="col-form-label">Empresa:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="empresa" name="empresa" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($empresas_filtro as $empresa): ?>
                        <option value="<?= htmlspecialchars($empresa) ?>" <?= ($empresa_seleccionada == $empresa) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa) ?>
                        </option>
                    <?php endforeach; ?>
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

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Empresa</th>
                                <th>Paciente</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recetas)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay recetas para mostrar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recetas as $i => $rec): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($rec['empresa']); ?></td>
                                        <td><?php echo htmlspecialchars($rec['nombre_paciente'] . ' ' . $rec['apellido_paciente']); ?></td>
                                        <td><?php echo htmlspecialchars($rec['NOM_PROD']); ?></td>
                                        <td><?php echo htmlspecialchars($rec['CANTIDAD']); ?></td>
                                        <td><?php echo htmlspecialchars($rec['MOTIVO']); ?></td>
                                        <td><?php echo htmlspecialchars($rec['FECHA_TRANSC']); ?></td>
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
            <input type="hidden" name="paciente" value="<?php echo htmlspecialchars($paciente_seleccionado); ?>">
            <input type="hidden" name="producto" value="<?php echo htmlspecialchars($producto_seleccionado); ?>">
            <input type="hidden" name="empresa" value="<?php echo htmlspecialchars($empresa_seleccionada); ?>">
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
    <!-- Scrips para Exportar pdf -->
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
    const title = "Reporte de Recetas Emitidas";
    doc.setFontSize(16);
    doc.text(title, 15, 15);
    doc.setFontSize(10);
    doc.text(`Generado el: ${new Date().toLocaleDateString()}`, 15, 22);
    doc.text(`Bodega: <?php echo $_SESSION['nombre_bodega']; ?>`, 15, 28);
    
    // Filtros aplicados
    <?php if($fecha_inicio && $fecha_fin): ?>
        doc.text(`Período: ${"<?php echo $fecha_inicio; ?>"} al ${"<?php echo $fecha_fin; ?>"}`, 15, 34);
    <?php endif; ?>
    <?php if($empresa_seleccionada): ?>
        doc.text(`Empresa: ${"<?php echo $empresa_seleccionada; ?>"}`, 100, 34);
    <?php endif; ?>

    // Datos de la tabla desde PHP
    const headers = [
        "#",
        "Empresa",
        "Paciente",
        "Producto",
        "Cantidad",
        "Motivo",
        "Fecha"
    ];

    const data = <?php echo json_encode($recetas); ?>.map((item, index) => [
        index + 1,
        item.empresa,
        `${item.nombre_paciente} ${item.apellido_paciente}`,
        item.NOM_PROD,
        item.CANTIDAD,
        item.MOTIVO,
        item.FECHA_TRANSC
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
            overflow: 'linebreak',
            valign: 'middle'
        },
        columnStyles: {
            0: { cellWidth: 8 },   // #
            1: { cellWidth: 25 },  // Empresa
            2: { cellWidth: 30 },  // Paciente
            3: { cellWidth: 30 },  // Producto
            4: { cellWidth: 15 },  // Cantidad
            5: { cellWidth: 30 },  // Motivo
            6: { cellWidth: 20 }   // Fecha
        },
        didParseCell: function(data) {
            // Ajustar altura de fila para contenido largo
            if (data.column.index === 5) { // Columna Motivo
                data.cell.height = Math.max(10, data.cell.text.length / 30 * 5);
            }
        }
    });

    doc.save(`Reporte_Recetas_${new Date().toISOString().slice(0,10)}.pdf`);
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