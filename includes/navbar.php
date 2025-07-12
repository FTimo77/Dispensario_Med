<?php
// Configuración del menú según roles
$nombre_rol = $_SESSION["nombre_rol"];

// Definir estructura del menú
$menu_items = [
    'inicio' => [
        'titulo' => 'Inicio',
        'icono' => 'bi-person-circle',
        'items' => [
            ['titulo' => 'Cerrar Sesión', 'url' => 'includes/logout.php', 'icono' => 'bi-box-arrow-right']
        ]
    ],
    'registro' => [
        'titulo' => 'Registro de Datos',
        'icono' => 'bi-pencil-square',
        'mostrar_para' => ['ADMIN', 'DOCTOR', 'ENFERMERA'],
        'items' => []
    ],
    'transacciones' => [
        'titulo' => 'Transacciones',
        'icono' => 'bi-arrow-left-right',
        'items' => [
            ['titulo' => 'Ingreso de Stock', 'url' => 'ingreso.php', 'icono' => 'bi-box-arrow-in-down'],
            [
                'titulo' => 'Egreso de Stock',
                'icono' => 'bi-box-arrow-up',
                'submenu' => [
                    ['titulo' => 'Por Atención', 'url' => 'egreso_unificado.php?tipo=normal', 'icono' => 'bi-person-check'],
                    ['titulo' => 'Por Botiquín', 'url' => 'egreso_unificado.php?tipo=botiquin', 'icono' => 'bi-prescription2']
                ]
            ]
        ]
    ],
    'reportes' => [
        'titulo' => 'Reportes',
        'icono' => 'bi-bar-chart',
        'items' => [
            ['titulo' => 'Existencias Mínimas', 'url' => 'reporte_lotes.php?tipo=minimos', 'icono' => 'bi-exclamation-circle'],
            ['titulo' => 'Movimientos', 'url' => 'reporte_movimientos.php', 'icono' => 'bi-arrow-down-up'],
            ['titulo' => 'Recetas emitidas', 'url' => 'reporte_recetas.php', 'icono' => 'bi-file-earmark-medical'],
            ['titulo' => 'Productos Caducados', 'url' => 'reporte_caducados.php', 'icono' => 'bi-calendar-x'],
            ['titulo' => 'General de productos', 'url' => 'reporte_lotes.php?tipo=general', 'icono' => 'bi-list-ul']
        ]
    ]
];

// Configurar items del menú Registro según rol
if ($nombre_rol === 'ADMIN') {
    $menu_items['registro']['items'] = [
        ['tipo' => 'header', 'titulo' => 'Administración'],
        ['titulo' => 'Usuarios', 'url' => 'users.php', 'icono' => 'bi-people'],
        ['titulo' => 'Pacientes', 'url' => 'paciente.php', 'icono' => 'bi-people'],
        ['titulo' => 'Roles', 'url' => 'rol_user.php', 'icono' => 'bi-people'],
        ['tipo' => 'header', 'titulo' => 'Inventario'],
        ['titulo' => 'Bodega', 'url' => 'agregar_bodega.php', 'icono' => 'bi-building'],
        ['titulo' => 'Productos', 'url' => 'producto.php', 'icono' => 'bi-box']
    ];
} elseif ($nombre_rol === 'DOCTOR' || $nombre_rol === 'ENFERMERA') {
    $menu_items['registro']['items'] = [
        ['titulo' => 'Pacientes', 'url' => 'paciente.php', 'icono' => 'bi-people'],
        ['tipo' => 'divider'],
        ['tipo' => 'header', 'titulo' => 'Inventario'],
        ['titulo' => 'Bodega', 'url' => 'agregar_bodega.php', 'icono' => 'bi-building'],
        ['titulo' => 'Productos', 'url' => 'producto.php', 'icono' => 'bi-box']
    ];
}

// Función para renderizar items del menú
function renderMenuItem($item) {
    if ($item['tipo'] ?? '' === 'header') {
        return '<li><h6 class="dropdown-header">' . htmlspecialchars($item['titulo']) . '</h6></li>';
    }
    
    if ($item['tipo'] ?? '' === 'divider') {
        return '<li><hr class="dropdown-divider" /></li>';
    }
    
    if (isset($item['submenu'])) {
        $html = '<li class="dropdown-submenu">';
        $html .= '<a class="dropdown-item dropdown-toggle" href="#">';
        $html .= '<i class="' . $item['icono'] . '"></i> ' . htmlspecialchars($item['titulo']);
        $html .= '</a>';
        $html .= '<ul class="dropdown-menu">';
        foreach ($item['submenu'] as $subitem) {
            $html .= renderMenuItem($subitem);
        }
        $html .= '</ul>';
        $html .= '</li>';
        return $html;
    }
    
    return '<li><a class="dropdown-item" href="' . $item['url'] . '">' .
           '<i class="' . $item['icono'] . '"></i> ' . htmlspecialchars($item['titulo']) . '</a></li>';
}

// Función para verificar si se debe mostrar un menú
function shouldShowMenu($menu_config, $user_role) {
    if (!isset($menu_config['mostrar_para'])) {
        return true;
    }
    return in_array($user_role, $menu_config['mostrar_para']);
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
  <div class="container">
    <a class="navbar-brand" href="menu_principal.php"><i class="bi bi-house-door"></i> Menú General</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
      aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        
        <?php foreach ($menu_items as $menu_key => $menu_config): ?>
          <?php if (shouldShowMenu($menu_config, $nombre_rol)): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="<?php echo $menu_key; ?>Dropdown" role="button" 
                 data-bs-toggle="dropdown" aria-expanded="false">
                <i class="<?php echo $menu_config['icono']; ?>"></i> <?php echo $menu_config['titulo']; ?>
              </a>
              <ul class="dropdown-menu" aria-labelledby="<?php echo $menu_key; ?>Dropdown">
                <?php foreach ($menu_config['items'] as $item): ?>
                  <?php echo renderMenuItem($item); ?>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
        
      </ul>
      
      <!-- Mostrar Usuario, bodega y rol en el Navbar -->
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