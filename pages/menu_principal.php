
<?php include __DIR__ . '/../controllers/menu_principal_controller.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Menú Principal</title>

    <link rel="icon" href="../assets/icons/capsule-pill.svg" type="image/x-icon">

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5 fade-in">
        <h2 class="text-center mb-4">Bienvenido al Menú Principal</h2>
        <p class="text-center">Productos proximos a caducar.</p>
    </div>


    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Categoria</th>
                                <th>Producto</th>
                                <th>Stock prod</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th>Caduca en (meses)</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if (empty($lotes)): ?>
        <tr>
            <td colspan="7" class="text-center">No hay lotes próximos a vencer.</td>
        </tr>
    <?php else:
        $i = 1;
        foreach ($lotes as $row): ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo htmlspecialchars($row['NOMBRE_CAT']); ?></td>
                <td><?php echo htmlspecialchars($row['NOM_PROD']); ?></td>
                <td><?php echo htmlspecialchars($row['STOCK_ACT_PROD']); ?></td>
                <td><?php echo htmlspecialchars($row['NUM_LOTE']); ?></td>
                <td><?php echo htmlspecialchars($row['FECH_VENC']); ?></td>
                <td style="font-weight: bold;
                    <?php
                        if ($row['MESES'] < 0) {
                            echo 'background-color: #dc3545; color: #fff;';
                        } elseif ($row['MESES'] < 3) {
                            echo 'background-color: #ffcccc;';
                        } elseif ($row['MESES'] < 6) {
                            echo 'background-color: #fff3cd;';
                        } elseif ($row['MESES'] < 9) {
                            echo 'background-color: #d4edda;';
                        }
                    ?>">
                    <?php
                        if ($row['MESES'] < 0) {
                            echo 'Caducado';
                        } else {
                            echo htmlspecialchars($row['MESES']);
                        }
                    ?>
                </td>
            </tr>
            <?php $i++;
        endforeach;
    endif; ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/models.js"></script>
    <script src="../js/navbar-submenu.js"></script>
</body>

</html>