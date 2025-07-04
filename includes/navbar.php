<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
  <div class="container">
    <a class="navbar-brand" href="menu_principal.php"><i class="bi bi-house-door"></i> Menú General</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
      aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Inicio -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="inicioDropdown" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-person-circle"></i> Inicio
          </a>
          <ul class="dropdown-menu" aria-labelledby="inicioDropdown">
            <li>
              <a class="dropdown-item" href="includes/logout.php">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
              </a>
            </li>
          </ul>
          <!-- Registro de datos -->
        </li><?php
        $nombre_rol = $_SESSION["nombre_rol"];

        if ($nombre_rol === 'admin' || $nombre_rol === 'doctor'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="registroDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="bi bi-pencil-square"></i> Registro de Datos
            </a>
            <ul class="dropdown-menu" aria-labelledby="registroDropdown">

              <?php if ($nombre_rol === 'admin'): ?>
                <li>
                  <h6 class="dropdown-header">Administración</h6>
                </li>
                <li><a class="dropdown-item" href="users.php"><i class="bi bi-people"></i> Usuarios</a></li>
                <li><a class="dropdown-item" href="paciente.php"><i class="bi bi-people"></i> Pacientes</a></li>
                <li><a class="dropdown-item" href="rol_user.php"><i class="bi bi-people"></i> Roles</a></li>
                <li>
                  <hr class="dropdown-divider" />
                </li>
              <?php elseif ($nombre_rol === 'doctor'): ?>
                <li><a class="dropdown-item" href="paciente.php"><i class="bi bi-people"></i> Pacientes</a></li>
              <?php endif; ?>

              <li>
                <h6 class="dropdown-header">Inventario</h6>
              </li>
              <li><a class="dropdown-item" href="agregar_bodega.php"><i class="bi bi-building"></i> Bodega</a></li>
              <li><a class="dropdown-item" href="producto.php"><i class="bi bi-box"></i> Productos</a></li>

            </ul>
          </li>
        <?php endif; ?>

        <!-- Transacciones -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="transaccionesDropdown" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-arrow-left-right"></i> Transacciones
          </a>
          <ul class="dropdown-menu" aria-labelledby="transaccionesDropdown">
            <li>
              <a class="dropdown-item" href="./ingreso.php"><i class="bi bi-box-arrow-in-down"></i> Ingreso de
                Stock</a>
            </li>
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="#">
                <i class="bi bi-box-arrow-up"></i> Egreso de Stock
              </a>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="egreso_unificado.php?tipo=normal">
                    <i class="bi bi-person-check"></i> Por Atención
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="egreso_unificado.php?tipo=botiquin">
                    <i class="bi bi-prescription2"></i> Por Botiquín
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </li>
        <!-- Reportes -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="reportesDropdown" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-bar-chart"></i> Reportes
          </a>
          <ul class="dropdown-menu" aria-labelledby="reportesDropdown">
            <li>
              <a class="dropdown-item" href="./reporte_lotes.php?tipo=minimos">
                <i class="bi bi-exclamation-circle"></i> Existencias Mínimas
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="reporte_movimientos.php"><i class="bi bi-arrow-down-up"></i>
                Movimientos</a>
            </li>
            <li>
              <a class="dropdown-item" href="reporte_recetas.php"><i class="bi bi-file-earmark-medical"></i> Recetas
                emitidas</a>
            </li>
            <li>
              <a class="dropdown-item" href="reporte_caducados.php"><i class="bi bi-calendar-x"></i> Productos
                Caducados</a>
            </li>
            <li>
              <a class="dropdown-item" href="./reporte_lotes.php?tipo=general">
                <i class="bi bi-list-ul"></i> General de productos
              </a>
            </li>
          </ul>
        </li>
      </ul>
      <!-- Agrega esto justo antes de cerrar el div .navbar-collapse -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <span class="nav-link disabled">
            <i class="bi bi-person-circle"></i>
            <?php
            if (isset($_SESSION['usuario'])) {
              echo htmlspecialchars($_SESSION['usuario']);
              if (!empty($_SESSION['bodega'])) {
                echo ' | <i class="bi bi-building"></i> ' . htmlspecialchars($_SESSION['nombre_bodega']);
              }
              if (!empty($_SESSION['rol'])) {
                echo ' | <i class="bi bi-person-badge"></i> ' . htmlspecialchars($_SESSION['nombre_rol']);
              }
            } else {
              echo "No autenticado";
            }
            ?>
          </span>
        </li>
      </ul>
    </div>
  </div>
</nav>