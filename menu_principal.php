<?php

  session_start();

if (!isset($_SESSION['usuario']) &&  !isset($_SESSION['bodega'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Menú Principal</title>
    
       <link rel="icon" href="./assets/icons/capsule-pill.svg" type="image/x-icon">

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  </head>
  <body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-5 fade-in">
      <h2 class="text-center mb-4">Bienvenido al Menú Principal</h2>
      <p class="text-center">Productos proximos a caducar.</p>
    </div>
<?php
require_once 'config/conexion.php';

// Iniciar sesión si no está iniciada (necesario para acceder a $_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conexion = new Conexion();
$conexion = $conexion->connect();

// Consulta SQL corregida
$sql = "
    SELECT 
        C.NOMBRE_CAT,
        P.NOM_PROD,
        P.STOCK_ACT_PROD,
        L.NUM_LOTE,
        L.FECH_VENC,
        DATEDIFF(L.FECH_VENC, NOW()) AS 'DIAS' 
    FROM 
        lote L
    JOIN 
        producto P ON L.ID_PROODUCTO = P.ID_PROODUCTO 
    JOIN 
        categoria C ON P.ID_CATEGORIA = C.ID_CATEGORIA 
    WHERE 
        DATEDIFF(L.FECH_VENC, NOW()) < 91 
        AND P.ESTADO_PROD = 1 
        AND P.CODIGO_BODEGA = ?
";

$stmt = $conexion->prepare($sql);

// Verificar si la preparación fue exitosa
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conexion->error);
}

// Obtener el valor de la bodega desde la sesión
$codigo_bodega = $_SESSION['bodega'] ?? null;

// Verificar que tenemos un valor válido para la bodega
if ($codigo_bodega === null) {
    die('Error: No se ha definido la bodega en la sesión');
}

// Vincular parámetro (seguro contra inyección SQL)
$stmt->bind_param("i", $codigo_bodega);

// Ejecutar consulta
if (!$stmt->execute()) {
    die('Error al ejecutar la consulta: ' . $stmt->error);
}

// Obtener resultados
$resultado = $stmt->get_result();

// Verificar si hay resultados
if ($resultado === false) {
    die('Error al obtener resultados: ' . $conexion->error);
}

// Cerrar statement
$stmt->close();

// Puedes eliminar el var_dump en producción
// var_dump($resultado->fetch_all(MYSQLI_ASSOC));
?>

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
                            <th>Caduca en</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado->num_rows == 0): ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay lotes próximos a vencer.</td>
                            </tr>
                        <?php else: 
                            $i = 0;
                            while ($row = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['NOMBRE_CAT']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NOM_PROD']); ?></td>
                                    <td><?php echo htmlspecialchars($row['STOCK_ACT_PROD']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NUM_LOTE']); ?></td>
                                    <td><?php echo htmlspecialchars($row['FECH_VENC']); ?></td>
                                    <td style="font-weight: bold; 
                                          <?php 
                                          if ($row['DIAS'] < 30) {
                                              echo 'background-color: #ffcccc;'; // Rojo claro
                                          } elseif ($row['DIAS'] < 60) {
                                              echo 'background-color: #fff3cd;'; // Amarillo claro
                                          } elseif ($row['DIAS'] < 91) {
                                              echo 'background-color: #d4edda;'; // Verde claro
                                          }
                                          ?>">
                                          <?php echo htmlspecialchars($row['DIAS']); ?>
                                      </td>
                                  </tr>
                            <?php 
                            $i++;
                            endwhile; 
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/models.js"></script>
    <script src="js/navbar-submenu.js"></script>
  </body>
</html>