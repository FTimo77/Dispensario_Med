<?php include __DIR__ . '/../controllers/bodega_controller.php'; ?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Bodegas</title>
    <link rel="icon" href="../assets/icons/capsule-pill.svg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <?php include '../includes/navbar.php'; ?>
    <div class="container py-5 fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 px-3 py-2 rounded"
                style="background: rgba(255,255,255,0.85); box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                Gestión de Bodegas
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearBodega">
                <i class="bi bi-plus-circle"></i> Crear Bodega
            </button>
        </div>
        <?php if ($mensaje) echo $mensaje; ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bodegas as $i => $bodega): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($bodega['codigo_bodega']) ?></td>
                                    <td><?= htmlspecialchars($bodega['descripcion']) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary me-2" title="Editar"
                                            onclick="editarBodega('<?= htmlspecialchars($bodega['codigo_bodega'], ENT_QUOTES) ?>', '<?= htmlspecialchars($bodega['descripcion'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="../controllers/bodega_controller.php?eliminar=<?= urlencode($bodega['codigo_bodega']) ?>"
                                            class="btn btn-sm btn-outline-danger" title="Eliminar"
                                            onclick="return confirm('¿Desea eliminar esta bodega?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bodegas)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay bodegas registradas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear bodega -->
    <div class="modal fade" id="modalCrearBodega" tabindex="-1" aria-labelledby="modalCrearBodegaLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="formCrearBodega" method="POST" action="../controllers/bodega_controller.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearBodegaLabel">Crear Bodega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="descripcion_bodega" class="form-label">Descripción</label>
                        <textarea rows="3" name="descripcion_bodega" id="descripcion_bodega" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar bodega -->
    <div class="modal fade" id="modalEditarBodega" tabindex="-1" aria-labelledby="modalEditarBodegaLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="formEditarBodega" method="POST" action="../controllers/bodega_controller.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarBodegaLabel">Editar Bodega</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="codigo_bodega" id="editar_codigo_bodega">
                    <div class="mb-3">
                        <label for="editar_descripcion_bodega" class="form-label">Descripción</label>
                        <textarea rows="3" name="editar_descripcion_bodega" id="editar_descripcion_bodega"
                            class="form-control" required></textarea>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
                <input type="hidden" name="editar_codigo_bodega" id="editar_codigo_bodega" value="">
            </form>
        </div>
    </div>

    <script src="../js/models.js"></script>
    <script src="../js/navbar-submenu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para abrir el modal de edición con los datos actuales
        function editarBodega(codigo, descripcion) {
    document.getElementById('editar_codigo_bodega').value = codigo;
    document.getElementById('editar_descripcion_bodega').value = descripcion;
    var modal = new bootstrap.Modal(document.getElementById('modalEditarBodega'));
    modal.show();
    }

    </script>
</body>
</html>