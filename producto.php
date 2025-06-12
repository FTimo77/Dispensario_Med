<?php
$mensaje = "";
if (isset($_GET['success'])) {
  if ($_GET['success'] == 1) {
    $mensaje = "Categoría y producto agregados correctamente.";
  } elseif ($_GET['success'] == 2) {
    $mensaje = "Producto eliminado correctamente.";
  } elseif ($_GET['success'] == 3) {
    $mensaje = "Categoría eliminada correctamente.";
  }
}

require_once 'config/conexion.php';
require_once 'includes/producto_model.php';

// Conexión
$conexion = new Conexion();
$conn = $conexion->connect();

// Eliminar producto (cambio de estado a 0)
if (isset($_GET['eliminar'])) {
  $idEliminar = intval($_GET['eliminar']);
  $stmt = $conn->prepare("UPDATE producto SET estado_prod=0 WHERE id_prooducto=?");
  $stmt->bind_param("i", $idEliminar);
  if ($stmt->execute()) {
    header("Location: producto.php?success=2");
    exit;
  } else {
    $mensaje = "Error al eliminar el producto.";
  }
  $stmt->close();
}

// Elimina categoría (cambio de estado a 0)
if (isset($_GET['eliminar_categoria'])) {
  $idCatEliminar = intval($_GET['eliminar_categoria']);
  $stmt = $conn->prepare("UPDATE categoria SET estado_cat=0 WHERE id_categoria=?");
  if (!$stmt) {
    $mensaje = "Error en la preparación de la consulta: " . $conn->error;
  } else {
    $stmt->bind_param("i", $idCatEliminar);
    if ($stmt->execute()) {
      header("Location: producto.php?success=3");
      exit;
    } else {
      // Error 1451 = restricción de clave foránea (productos asociados)
      if ($conn->errno == 1451) {
        $mensaje = "No se puede eliminar la categoría porque tiene productos asociados.";
      } else {
        $mensaje = "Error al eliminar la categoría.";
      }
    }
    $stmt->close();
  }
}

// Cargar categorías para el select
$categorias = [];
$result = $conn->query("SELECT id_categoria, nombre_cat FROM categoria WHERE estado_cat = 1");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
  }
}

// Procesamiento del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre = trim($_POST['productname']);
  $presentacion = trim($_POST['presentacionproducto']);
  $categoria_id = trim($_POST['categoriaSeleccionada']);
  $nueva_categoria = trim($_POST['nueva_categoria']);

  if ($nombre !== "" && $presentacion !== "" && ($categoria_id !== "" || $nueva_categoria !== "")) {
    // Si se ingresó una nueva categoría
    if ($nueva_categoria !== "") {
      $stmt_cat = $conn->prepare("INSERT INTO categoria (nombre_cat, estado_cat) VALUES (?, 1)");
      $stmt_cat->bind_param("s", $nueva_categoria);
      if ($stmt_cat->execute()) {
        $categoria_id = $conn->insert_id;
        $stmt_cat->close();
      } else {
        $mensaje = "Error al crear la categoría.";
        $stmt_cat->close();
      }
    }
    // Insertar producto solo si hay un id de categoría válido
    if ($categoria_id !== "") {
      if (agregarProducto($conn, $nombre, $presentacion, $categoria_id)) {
        if ($mensaje == "") {
          $mensaje = "Producto creado correctamente.";
        }
        header("Location: producto.php?success=1");
        exit;
      } else {
        $mensaje = "Error al crear el producto.";
      }
    }
  } else {
    $mensaje = "Todos los campos son obligatorios.";
  }
}

// Cargar productos con su categoría usando el modelo
$productos = obtenerProductos($conn);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crear Producto</title>
  <link rel="icon" href="./assets/icons/capsule-pill.svg" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
</head>

<body class="bg-light">
  <div id="navbar"></div>
  <div class="container py-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 px-3 py-2 rounded"
        style="background: rgba(255,255,255,0.85); box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        Gestión de Productos
      </h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
        <i class="bi bi-plus-circle"></i> Crear Producto
      </button>
    </div>
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
                <th>Nombre del Producto</th>
                <th>Presentación</th>
                <th>Categoría</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $i => $prod): ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><?php echo htmlspecialchars($prod['NOM_PROD']); ?></td>
                  <td><?php echo htmlspecialchars($prod['PRESENTACION_PROD']); ?></td>
                  <td><?php echo htmlspecialchars($prod['nombre_cat']); ?></td>
                  <td class="text-end">
                    <!-- Icono Editar -->
                    <a href="#" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <!-- Icono Eliminar -->
                    <a href="producto.php?eliminar=<?php echo $prod['id_prooducto']; ?>"
                      class="btn btn-sm btn-outline-danger" title="Eliminar"
                      onclick="return confirm('¿Desea eliminar este producto?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para crear producto -->
  <div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalCrearProductoLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="formularioProducto" method="POST" action=""
        onsubmit="return finalizarFormulario();">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCrearProductoLabel">Crear Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="productname" class="form-label">Nombre del producto</label>
              <input type="text" class="form-control" id="productname" name="productname" placeholder="Ej. Paracetamol"
                required />
            </div>
            <div class="col-12">
              <label for="presentacionproducto" class="form-label">Presentación del producto</label>
              <input type="text" class="form-control" id="presentacionproducto" name="presentacionproducto"
                placeholder="Ej. Caja de 6 unidades" required />
            </div>
            <div class="col-12">
              <label for="categoriaSeleccionada" class="form-label">Categoría</label>
              <div class="input-group mb-2">
                <select class="form-control" id="categoriaSeleccionada" name="categoriaSeleccionada">
                  <option value="">Seleccione una categoría</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id_categoria']; ?>">
                      <?php echo htmlspecialchars($cat['nombre_cat']); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#modalCategorias">
                  <i class="bi bi-gear"></i>
                </button>
              </div>
              <input type="text" class="form-control mt-2" id="nueva_categoria" name="nueva_categoria"
                placeholder="O escriba una nueva categoría" />
              <small class="text-muted">Seleccione una categoría existente o escriba una nueva.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Finalizar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal para administrar categorías (fuera de cualquier otro modal) -->
  <div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCategoriasLabel">Administrar Categorías</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Nombre de la categoría</th>
                  <th class="text-end">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categorias as $cat): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($cat['nombre_cat']); ?></td>
                    <td class="text-end">
                      <a href="producto.php?eliminar_categoria=<?php echo $cat['id_categoria']; ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('¿Desea eliminar esta categoría?');">
                        <i class="bi bi-trash"></i> Eliminar
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <script src="js/models.js"></script>
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