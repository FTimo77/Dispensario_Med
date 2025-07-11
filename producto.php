<?php

session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once 'config/conexion.php';
require_once 'includes/producto_model.php';

// Conexión
$conexion = new Conexion();
$conn = $conexion->connect();

// Manejar la solicitud AJAX para validar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre']) && !isset($_POST['productname'])) {
    header('Content-Type: application/json');
    
    try {
        $nombre = trim($conn->real_escape_string($_POST['nombre']));
        $bodega = $_SESSION['bodega'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto 
                              WHERE NOM_PROD = ? 
                              AND CODIGO_BODEGA = ? 
                              AND estado_prod = 1");
        $stmt->bind_param("si", $nombre, $bodega);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo json_encode([
            'existe' => $result->fetch_assoc()['total'] > 0,
            'nombre' => $nombre
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'error' => 'Error al verificar el producto: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Resto de tu código PHP actual...

$mensaje = "";
if (isset($_GET['success'])) {
  if ($_GET['success'] == 1) {
    $mensaje = "Categoría y producto agregados correctamente.";
  } elseif ($_GET['success'] == 2) {
    $mensaje = "Producto eliminado correctamente.";
  } elseif ($_GET['success'] == 3) {
    $mensaje = "Categoría eliminada correctamente.";
  } elseif ($_GET['success'] == 4) {
    $mensaje = "Producto actualizado correctamente.";
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
  $id_producto_editar = isset($_POST['id_producto_editar']) ? trim($_POST['id_producto_editar']) : '';
  $nombre = trim($_POST['productname']);
  $presentacion = trim($_POST['presentacionproducto']);
  $medida_cantidad = trim($_POST['medida_cantidad']);
  $medida_unidad = trim($_POST['medida_unidad']);
  $categoria_id = trim($_POST['categoriaSeleccionada']);
  $nueva_categoria = trim($_POST['nueva_categoria']);
  $stock_minimo = trim($_POST['stockminimo']);

  // Concatenar medida a la presentación si se ingresó
  if ($medida_cantidad !== '' && $medida_unidad !== '') {
    $presentacion .= ' - ' . $medida_cantidad . ' ' . $medida_unidad;
  }

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
    // Si es edición
    if ($id_producto_editar !== "") {
      $stmt = $conn->prepare("UPDATE producto SET NOM_PROD=?, PRESENTACION_PROD=?, id_categoria=?, stock_min_prod=? WHERE id_prooducto=?");
      if (!$stmt) {
        $mensaje = "Error en la preparación de la consulta: " . $conn->error;
      } else {
        $stmt->bind_param("ssiii", $nombre, $presentacion, $categoria_id, $stock_minimo, $id_producto_editar);
        if ($stmt->execute()) {
          header("Location: producto.php?success=4");
          exit;
        } else {
          $mensaje = "Error al actualizar el producto.";
        }
        $stmt->close();
      }
    } else {
      // Insertar producto solo si hay un id de categoría válido
      if ($categoria_id !== "") {
        if (agregarProducto($conn, $nombre, $presentacion, $categoria_id, $_SESSION['bodega'], $stock_minimo)) {
          if ($mensaje == "") {
            $mensaje = "Producto creado correctamente.";
          }
          header("Location: producto.php?success=1");
          exit;
        } else {
          $mensaje = "Error al crear el producto.";
        }
      }
    }
  } else {
    $mensaje = "Todos los campos son obligatorios.";
  }
}

// Cargar productos con su categoría usando el modelo
$productos = obtenerProductos($conn,$_SESSION['bodega']);
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
  <?php include 'includes/navbar.php'; ?>
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
                    <button type="button" class="btn btn-sm btn-outline-primary me-2 btn-editar-producto" title="Editar"
                      data-id="<?php echo $prod['id_prooducto']; ?>"
                      data-nombre="<?php echo htmlspecialchars($prod['NOM_PROD']); ?>"
                      data-presentacion="<?php echo htmlspecialchars($prod['PRESENTACION_PROD']); ?>"
                      data-categoria="<?php echo $prod['id_categoria'] ?? ''; ?>"
                      data-stockmin="<?php echo htmlspecialchars($prod['stock_minimo'] ?? ''); ?>">
                      <i class="bi bi-pencil-square"></i>
                    </button>
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
        <input type="hidden" id="id_producto_editar" name="id_producto_editar" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCrearProductoLabel">Crear Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="productname" class="form-label">Nombre del producto</label>
              <input type="text" class="form-control uppercase-input" id="productname" name="productname" placeholder="Ej. Paracetamol"
                required oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()" 
                onblur="validarProductoExistente(this)">
            </div>
            <div class="col-12">
              <label for="presentacionproducto" class="form-label">Presentación del producto</label>
              <select class="form-control" id="presentacionproducto" name="presentacionproducto" required>
                <option value="">Seleccione una presentación</option>
                <option value="SOLIDO ORAL">SOLIDO ORAL</option>
                <option value="LIQUIDO ORAL">LIQUIDO ORAL</option>
                <option value="SOLUCION PARENTAL">SOLUCION PARENTAL</option>
                <option value="SOLUCION INYECTABLE">SOLUCION INYECTABLE</option>
                <option value="SEMISOLIDO CUTANEO">SEMISOLIDO CUTANEO</option>
                <option value="SOLIDO PARENTERAL">SOLIDO PARENTERAL</option>
                <option value="SOLUCION OFTALMICA">SOLUCION OFTALMICA</option>
                <option value="SEMISOLIDO OFTALMICO">SEMISOLIDO OFTALMICO</option>
                <option value="SOLUCION OTICA">SOLUCION OTICA</option>
                <option value="SOLUCION ORAL">SOLUCION ORAL</option>
                <option value="SUSPENSION ORAL">SUSPENSION ORAL</option>
                <option value="SUSPENSION OFTALMICA">SUSPENSION OFTALMICA</option>
              </select>
            </div>
            <div class="col-12 d-flex align-items-end mb-2">
              <div style="flex:2;">
                <label for="medida_cantidad" class="form-label">Cantidad de la medida</label>
                <input type="number" min="0" step="any" class="form-control" id="medida_cantidad" name="medida_cantidad" placeholder="Ej. 500" />
              </div>
              <div style="flex:1; margin-left:10px;">
                <label for="medida_unidad" class="form-label">Unidad</label>
                <select class="form-select" id="medida_unidad" name="medida_unidad">
                  <option value="">Unidad</option>
                  <option value="MG">MG</option>
                  <option value="G">G</option>
                  <option value="ML">ML</option>
                  <option value="L">L</option>
                  <option value="UI">UI</option>
                  <option value="MCG">MCG</option>
                </select>
              </div>
            </div>
            <div class="col-12">
              <label for="categoriaSeleccionada" class="form-label">Categoría</label>
              <div class="input-group mb-2">
                <select class="form-control" id="categoriaSeleccionada" name="categoriaSeleccionada">
                  <option value="">Seleccione una categoría</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id_categoria']; ?>">
                      <?php echo htmlspecialchars($cat['nombre_cat']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#modalCategorias">
                  <i class="bi bi-gear"></i>
                </button>
              </div>
              <input type="text" class="form-control mt-2 uppercase-input" id="nueva_categoria" name="nueva_categoria"
                placeholder="O escriba una nueva categoría" oninput="letrasYEspacios(this); this.value= this.value.toUpperCase()" />
              <small class="text-muted">Seleccione una categoría existente o escriba una nueva.</small>
            </div>
            <div class="col-12">
              <label for="stockmin" class="form-label">Stock Mínimo</label>
              <input type="number" class="form-control" id="stockmin" name="stockminimo"
                placeholder="Ej. 1" required />
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
  <script src="js/navbar-submenu.js"></script>
    <script src="js/valitationInputs.js"></script><!--valida inputs -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- EDICIÓN DE PRODUCTO ---
    document.querySelectorAll('.btn-editar-producto').forEach(btn => {
      btn.addEventListener('click', function() {
        // Rellenar el modal con los datos del producto
        document.getElementById('id_producto_editar').value = this.dataset.id;
        document.getElementById('productname').value = this.dataset.nombre;
        // Separar presentación y medida si existe
        let presentacion = this.dataset.presentacion;
        let medidaCantidad = '';
        let medidaUnidad = '';
        if (presentacion && presentacion.includes(' - ')) {
          const partes = presentacion.split(' - ');
          document.getElementById('presentacionproducto').value = partes[0];
          if (partes[1]) {
            const match = partes[1].match(/^(\d+(?:[.,]\d+)?)\s*(\w+)$/);
            if (match) {
              medidaCantidad = match[1];
              medidaUnidad = match[2];
            }
          }
        } else {
          document.getElementById('presentacionproducto').value = presentacion;
        }
        document.getElementById('medida_cantidad').value = medidaCantidad;
        document.getElementById('medida_unidad').value = medidaUnidad;
        document.getElementById('categoriaSeleccionada').value = this.dataset.categoria;
        document.getElementById('stockmin').value = this.dataset.stockmin;
        document.getElementById('modalCrearProductoLabel').textContent = 'Editar Producto';
        var modal = new bootstrap.Modal(document.getElementById('modalCrearProducto'));
        modal.show();
      });
    });
    // Al cerrar el modal, limpiar el formulario y el modo
    document.getElementById('modalCrearProducto').addEventListener('hidden.bs.modal', function () {
      document.getElementById('formularioProducto').reset();
      document.getElementById('id_producto_editar').value = '';
      document.getElementById('modalCrearProductoLabel').textContent = 'Crear Producto';
    });

function validarProductoExistente(input) {
    const nombre = input.value.trim();
    const errorDiv = document.getElementById('producto-error');
    
    // Resetear mensaje de error
    if (errorDiv) {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }
    
    if (!nombre) return;

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'nombre=' + encodeURIComponent(nombre)
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        return response.json();
    })
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            return;
        }
        
        if (data.existe) {
            // Mostrar alerta con un solo botón OK
            alert(`El producto "${data.nombre}" ya existe en la bodega`);
            
            // Estas acciones se ejecutarán después de hacer clic en OK
            document.getElementById('formularioProducto').reset();
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearProducto'));
            if (modal) {
                modal.hide();
            }
        }
    })
    .catch(error => {
        console.error('Error al validar producto:', error);
        if (errorDiv) {
            errorDiv.textContent = 'Error al verificar el producto';
            errorDiv.style.display = 'block';
        }
    });
}
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