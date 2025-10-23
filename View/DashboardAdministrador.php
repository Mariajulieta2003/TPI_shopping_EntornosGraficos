<?php
session_start();
require_once '../Model/conexion.php';


// Funciones para el dashboard del administrador

// Manejo de acciones administrativas vía POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    // Verificar sesión y rol
    session_start();
    if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        header('Content-Type: application/json; charset=utf-8', true, 403);
        echo json_encode(['error' => 'Permiso denegado']);
        exit;
    }

    require_once __DIR__ . '/../Model/conexion.php';
    $pdo = getConnection();
    $action = $_POST['action'];

    header('Content-Type: application/json; charset=utf-8');

    try {
        switch ($action) {
            // LOCALES
            case 'create_local':
                $nombre = trim($_POST['nombre'] ?? '');
                $usuarioFK = !empty($_POST['usuarioFK']) ? intval($_POST['usuarioFK']) : null;
                $ubicacionFK = !empty($_POST['ubicacionFK']) ? intval($_POST['ubicacionFK']) : null;
                $rubro = trim($_POST['rubro'] ?? '');
                if ($nombre === '') throw new Exception('Nombre de local requerido');
                $stmt = $pdo->prepare("INSERT INTO local (nombre, usuarioFK, ubicacionFK, rubro) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $usuarioFK, $ubicacionFK, $rubro]);
                echo json_encode(['success' => true, 'IDlocal' => $pdo->lastInsertId()]);
                break;

            case 'update_local':
                $id = intval($_POST['IDlocal'] ?? 0);
                if ($id <= 0) throw new Exception('IDlocal inválido');
                $nombre = trim($_POST['nombre'] ?? '');
                $usuarioFK = !empty($_POST['usuarioFK']) ? intval($_POST['usuarioFK']) : null;
                $ubicacionFK = !empty($_POST['ubicacionFK']) ? intval($_POST['ubicacionFK']) : null;
                $rubro = trim($_POST['rubro'] ?? '');
                $stmt = $pdo->prepare("UPDATE local SET nombre = ?, usuarioFK = ?, ubicacionFK = ?, rubro = ? WHERE IDlocal = ?");
                $stmt->execute([$nombre, $usuarioFK, $ubicacionFK, $rubro, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'delete_local':
                $id = intval($_POST['IDlocal'] ?? 0);
                if ($id <= 0) throw new Exception('IDlocal inválido');
                $stmt = $pdo->prepare("DELETE FROM local WHERE IDlocal = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
                break;

            // VALIDAR CUENTAS (DUEÑOS)
            case 'validate_user':
                $userId = intval($_POST['IDusuario'] ?? 0);
                $approve = isset($_POST['approve']) && $_POST['approve'] == '1' ? 1 : 0;
                if ($userId <= 0) throw new Exception('IDusuario inválido');
                $stmt = $pdo->prepare("UPDATE usuario SET estado = ? WHERE IDusuario = ?");
                $stmt->execute([$approve, $userId]);
                echo json_encode(['success' => true]);
                break;

            // SOLICITUDES DE DESCUENTO
            case 'decide_solicitud':
                $id = intval($_POST['IDsolicitud'] ?? 0);
                $approve = isset($_POST['approve']) && $_POST['approve'] == '1' ? 1 : 0;
                if ($id <= 0) throw new Exception('IDsolicitud inválido');
                // Asumir columna 'estado' en solicitud: 1=aprobada,2=rechazada,0=pendiente
                $estado = $approve ? 1 : 2;
                $stmt = $pdo->prepare("UPDATE solicitud SET estado = ? WHERE IDsolicitud = ?");
                $stmt->execute([$estado, $id]);
                echo json_encode(['success' => true]);
                break;

            // NOVEDADES
            case 'create_novedad':
                $titulo = trim($_POST['titulo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $desde = $_POST['desde'] ?? null;
                $hasta = $_POST['hasta'] ?? null;
                if ($titulo === '') throw new Exception('Título requerido');
                $stmt = $pdo->prepare("INSERT INTO novedad (titulo, descripcion, desde, hasta) VALUES (?, ?, ?, ?)");
                $stmt->execute([$titulo, $descripcion, $desde, $hasta]);
                echo json_encode(['success' => true, 'IDnovedad' => $pdo->lastInsertId()]);
                break;

            case 'update_novedad':
                $id = intval($_POST['IDnovedad'] ?? 0);
                if ($id <= 0) throw new Exception('IDnovedad inválido');
                $titulo = trim($_POST['titulo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $desde = $_POST['desde'] ?? null;
                $hasta = $_POST['hasta'] ?? null;
                $stmt = $pdo->prepare("UPDATE novedad SET titulo = ?, descripcion = ?, desde = ?, hasta = ? WHERE IDnovedad = ?");
                $stmt->execute([$titulo, $descripcion, $desde, $hasta, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'delete_novedad':
                $id = intval($_POST['IDnovedad'] ?? 0);
                if ($id <= 0) throw new Exception('IDnovedad inválido');
                $stmt = $pdo->prepare("DELETE FROM novedad WHERE IDnovedad = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
                break;

            default:
                echo json_encode(['error' => 'Acción no válida']);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener estadísticas generales
function getEstadisticasGenerales() {
    $pdo = getConnection();
    
    $stats = [];
    
    // Total de locales
    $query = "SELECT COUNT(*) as total FROM local";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['total_locales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Solicitudes pendientes de validación
    $query = "SELECT COUNT(*) as total FROM solicitud";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['solicitudes_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Promociones pendientes de aprobación
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['promociones_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de novedades activas
    $query = "SELECT COUNT(*) as total FROM novedad WHERE hasta >= CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['novedades_activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Usos de promociones (últimos 30 días)
    $query = "SELECT COUNT(*) as total FROM usopromocion WHERE fechaUso >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['usos_30_dias'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

// Obtener locales para gestión
function getLocales() {
    $pdo = getConnection();
    $query = "
        SELECT l.*, u.nombreUsuario as dueño, ub.nombre as ubicacion_nombre 
        FROM local l 
        LEFT JOIN usuario u ON l.usuarioFK = u.IDusuario 
        LEFT JOIN ubicacion ub ON l.ubicacionFK = ub.IDubicacion 
        ORDER BY l.nombre
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener solicitudes pendientes de dueños
function getSolicitudesPendientes() {
    $pdo = getConnection();
    $query = "SELECT * FROM solicitud ORDER BY IDsolicitud DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener promociones pendientes de aprobación
function getPromocionesPendientes() {
    $pdo = getConnection();
    $query = "
        SELECT p.*, l.nombre as local_nombre, l.rubro as local_rubro 
        FROM promocion p 
        INNER JOIN local l ON p.localFk = l.IDlocal 
        WHERE p.estado = 0 
        ORDER BY p.desde ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener novedades activas
function getNovedadesActivas() {
    $pdo = getConnection();
    $query = "SELECT * FROM novedad WHERE hasta >= CURDATE() ORDER BY desde DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener reporte de uso de descuentos
function getReporteUsos($filtros = []) {
    $pdo = getConnection();
    
    $whereConditions = ["1=1"];
    $params = [];
    
    // Filtro por fecha desde
    if (!empty($filtros['fecha_desde'])) {
        $whereConditions[] = "up.fechaUso >= ?";
        $params[] = $filtros['fecha_desde'];
    }
    
    // Filtro por fecha hasta
    if (!empty($filtros['fecha_hasta'])) {
        $whereConditions[] = "up.fechaUso <= ?";
        $params[] = $filtros['fecha_hasta'];
    }
    
    // Filtro por local
    if (!empty($filtros['local_id'])) {
        $whereConditions[] = "p.localFk = ?";
        $params[] = $filtros['local_id'];
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    $query = "
        SELECT 
            up.fechaUso,
            up.estado,
            u.nombreUsuario,
            u.DNI,
            c.nombre as categoria_usuario,
            p.descripcion as promocion_descripcion,
            p.categoriaHabilitada,
            l.nombre as local_nombre,
            l.rubro as local_rubro
        FROM usopromocion up
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion
        INNER JOIN usuario u ON up.usuarioFk = u.IDusuario
        INNER JOIN categoria c ON u.categoriaFK = c.IDcategoria
        INNER JOIN local l ON p.localFk = l.IDlocal
        WHERE $whereClause
        ORDER BY up.fechaUso DESC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener todos los locales para filtros
function getAllLocales() {
    $pdo = getConnection();
    $query = "SELECT IDlocal, nombre FROM local ORDER BY nombre";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$estadisticas = getEstadisticasGenerales();
$locales = getLocales();
$solicitudesPendientes = getSolicitudesPendientes();
$promocionesPendientes = getPromocionesPendientes();
$novedadesActivas = getNovedadesActivas();
$todosLocales = getAllLocales();

// Procesar filtros para reportes
$filtrosReporte = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtro_reporte'])) {
    $filtrosReporte = [
        'fecha_desde' => $_POST['fecha_desde'] ?? '',
        'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
        'local_id' => $_POST['local_id'] ?? ''
    ];
} else {
    // Por defecto, último mes
    $filtrosReporte = [
        'fecha_desde' => date('Y-m-d', strtotime('-1 month')),
        'fecha_hasta' => date('Y-m-d'),
        'local_id' => ''
    ];
}

$reporteUsos = getReporteUsos($filtrosReporte);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - ShoppingGenerico</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4A3BC7;
            --primary-rgb: 74, 59, 199;
            --subtle: #F3F1FF;
            --muted: #6c6c6c;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }

        body {
            background: linear-gradient(180deg, #fff 0%, var(--subtle) 100%);
            min-height: 100vh;
        }

        .navbar, .card-header {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: #3A2BA7;
            border-color: #3A2BA7;
        }

        .sidebar {
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #e9e9ef;
        }

        .sidebar .nav-link {
            color: #333;
        }

        .sidebar .nav-link.active {
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--primary);
            border-radius: .5rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .quick-action-card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            height: 100%;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .estado-activo { background-color: var(--success); }
        .estado-inactivo { background-color: var(--danger); }
        .estado-pendiente { background-color: var(--warning); color: #000; }

        .table-hover tbody tr:hover {
            background-color: rgba(var(--primary-rgb), 0.04);
        }

        .filtros-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 3px solid var(--primary);
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <strong>ShoppingGenerico - Administrador</strong>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <strong class="me-2"><?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Admin'); ?></strong>
                        <span class="badge bg-light text-primary">Administrador</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="./DashboardAdmin.php">Inicio</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../Model/logout.php">Cerrar sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-12 col-md-3 col-lg-2 px-3 sidebar">
                <div class="pt-3 pb-2">
                    <h6 class="text-muted">Panel de Administración</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="./DashboardAdmin.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./GestionLocales.php">
                                <i class="bi bi-shop me-2"></i>Gestión de Locales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./ValidarCuentas.php">
                                <i class="bi bi-person-check me-2"></i>Validar Cuentas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./AprobarSolicitudes.php">
                                <i class="bi bi-clipboard-check me-2"></i>Aprobar Solicitudes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./GestionNovedades.php">
                                <i class="bi bi-megaphone me-2"></i>Gestión de Novedades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./Reportes.php">
                                <i class="bi bi-graph-up me-2"></i>Reportes
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-12 col-md-9 col-lg-10 py-4">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-speedometer2 me-2" style="color: var(--primary);"></i>
                                Dashboard de Administración
                            </h1>
                            <p class="text-muted mb-0">Gestión completa del sistema ShoppingGenerico</p>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Última actualización</div>
                            <div class="fw-medium"><?php echo date('d/m/Y H:i'); ?></div>
                        </div>
                    </div>

                    <!-- Estadísticas Principales -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Locales</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo $estadisticas['total_locales']; ?></div>
                                    </div>
                                    <i class="bi bi-shop" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Solicitudes</h6>
                                        <div class="h4 mb-0 text-warning"><?php echo $estadisticas['solicitudes_pendientes']; ?></div>
                                    </div>
                                    <i class="bi bi-person-plus" style="font-size: 1.5rem; color: var(--warning);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Promociones Pend.</h6>
                                        <div class="h4 mb-0 text-info"><?php echo $estadisticas['promociones_pendientes']; ?></div>
                                    </div>
                                    <i class="bi bi-ticket-perforated" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Novedades Activas</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticas['novedades_activas']; ?></div>
                                    </div>
                                    <i class="bi bi-megaphone" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Usos (30 días)</h6>
                                        <div class="h4 mb-0 text-danger"><?php echo $estadisticas['usos_30_dias']; ?></div>
                                    </div>
                                    <i class="bi bi-graph-up-arrow" style="font-size: 1.5rem; color: var(--danger);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Acciones Rápidas</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="./GestionLocales.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-plus-circle" style="font-size: 2rem; color: var(--primary);"></i>
                                            <h6 class="mt-2 mb-1">Crear Local</h6>
                                            <p class="text-muted small mb-0">Agregar nuevo local al sistema</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./ValidarCuentas.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-person-check" style="font-size: 2rem; color: var(--success);"></i>
                                            <h6 class="mt-2 mb-1">Validar Cuentas</h6>
                                            <p class="text-muted small mb-0">Revisar solicitudes de dueños</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./AprobarSolicitudes.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-clipboard-check" style="font-size: 2rem; color: var(--warning);"></i>
                                            <h6 class="mt-2 mb-1">Aprobar Promociones</h6>
                                            <p class="text-muted small mb-0">Revisar promociones pendientes</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./GestionNovedades.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-megaphone" style="font-size: 2rem; color: var(--info);"></i>
                                            <h6 class="mt-2 mb-1">Crear Novedad</h6>
                                            <p class="text-muted small mb-0">Publicar novedad del shopping</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Locales Recientes & Gestión (CRUD) -->
                    <div class="row">
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Gestión de Locales</h5>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalLocal" onclick="openCreateLocal()">
                                            <i class="bi bi-plus-circle"></i> Nuevo
                                        </button>
                                        <a href="./GestionLocales.php" class="btn btn-sm btn-primary">Ver Todos</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($locales)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-shop" style="font-size: 2rem; color: var(--muted);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay locales registrados</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Dueño</th>
                                                        <th>Ubicación</th>
                                                        <th class="text-end">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($locales, 0, 8) as $local): ?>
                                                        <tr id="local-row-<?php echo $local['IDlocal']; ?>">
                                                            <td>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($local['nombre']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($local['rubro']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($local['dueño'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($local['ubicacion_nombre'] ?? 'N/A'); ?></td>
                                                            <td class="text-end">
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-primary action-btn" title="Editar" onclick="openEditLocal(<?php echo $local['IDlocal']; ?>)">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger action-btn" title="Eliminar" onclick="confirmDeleteLocal(<?php echo $local['IDlocal']; ?>)">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Solicitudes Pendientes (Validar cuentas / Solicitudes de dueños) -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Solicitudes y Validaciones</h5>
                                    <span class="badge bg-warning"><?php echo count($solicitudesPendientes); ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($solicitudesPendientes)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--success);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay solicitudes pendientes</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group">
                                            <?php foreach (array_slice($solicitudesPendientes, 0, 8) as $sol): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-start" id="solicitud-<?php echo $sol['IDsolicitud']; ?>">
                                                    <div class="me-3">
                                                        <strong><?php echo htmlspecialchars($sol['nombre'] ?? $sol['nombreLocal'] ?? 'Solicitud'); ?></strong>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($sol['email'] ?? ''); ?></div>
                                                        <div class="small text-muted">Local: <?php echo htmlspecialchars($sol['nombreLocal'] ?? 'N/A'); ?></div>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-success" title="Aprobar" onclick="decideSolicitud(<?php echo $sol['IDsolicitud']; ?>, 1)">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button class="btn btn-danger" title="Rechazar" onclick="decideSolicitud(<?php echo $sol['IDsolicitud']; ?>, 0)">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <hr>

                                    <!-- Promociones Pendientes (Aprobar/Denegar) -->
                                    <h6 class="mt-3">Promociones Pendientes</h6>
                                    <?php if (empty($promocionesPendientes)): ?>
                                        <p class="text-muted small mb-0">No hay promociones pendientes de aprobación</p>
                                    <?php else: ?>
                                        <div class="list-group mt-2">
                                            <?php foreach (array_slice($promocionesPendientes, 0, 6) as $promo): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($promo['descripcion']); ?></strong>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($promo['local_nombre']); ?> — <?php echo htmlspecialchars($promo['rubro']); ?></div>
                                                        <div class="small text-muted">Desde: <?php echo htmlspecialchars($promo['desde']); ?> Hasta: <?php echo htmlspecialchars($promo['hasta']); ?></div>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-success" onclick="decidePromocion(<?php echo $promo['IDpromocion']; ?>, 1)"><i class="bi bi-check-lg"></i></button>
                                                        <button class="btn btn-danger" onclick="decidePromocion(<?php echo $promo['IDpromocion']; ?>, 0)"><i class="bi bi-x-lg"></i></button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de Novedades (CRUD rápido) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Novedades</h5>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNovedad" onclick="openCreateNovedad()">
                                        <i class="bi bi-plus-circle"></i> Nueva Novedad
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($novedadesActivas)): ?>
                                        <p class="text-muted">No hay novedades activas</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Título</th>
                                                        <th>Vigencia</th>
                                                        <th class="text-end">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($novedadesActivas as $nov): ?>
                                                        <tr id="novedad-<?php echo $nov['IDnovedad']; ?>">
                                                            <td><?php echo htmlspecialchars($nov['titulo']); ?></td>
                                                            <td><?php echo htmlspecialchars($nov['desde']) . ' → ' . htmlspecialchars($nov['hasta']); ?></td>
                                                            <td class="text-end">
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-primary" onclick="openEditNovedad(<?php echo $nov['IDnovedad']; ?>)"><i class="bi bi-pencil"></i></button>
                                                                    <button class="btn btn-outline-danger" onclick="confirmDeleteNovedad(<?php echo $nov['IDnovedad']; ?>)"><i class="bi bi-trash"></i></button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte de Usos Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Reporte de Usos de Promociones</h5>
                        </div>
                        <div class="card-body">
                            <!-- Filtros -->
                            <div class="filtros-card p-3 mb-4">
                                <form method="POST" id="filtrosForm">
                                    <input type="hidden" name="filtro_reporte" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                            <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                                   value="<?php echo htmlspecialchars($filtrosReporte['fecha_desde']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                            <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                                   value="<?php echo htmlspecialchars($filtrosReporte['fecha_hasta']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="local_id" class="form-label">Local</label>
                                            <select class="form-select" id="local_id" name="local_id">
                                                <option value="">Todos los locales</option>
                                                <?php foreach ($todosLocales as $local): ?>
                                                    <option value="<?php echo $local['IDlocal']; ?>" 
                                                        <?php echo $filtrosReporte['local_id'] == $local['IDlocal'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($local['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bi bi-filter me-1"></i>Filtrar
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Tabla de Reportes -->
                            <?php if (empty($reporteUsos)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No se encontraron registros</h5>
                                    <p class="text-muted">No hay usos de promociones que coincidan con los filtros aplicados.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Local</th>
                                                <th>Promoción</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reporteUsos as $registro): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo date('d/m/Y', strtotime($registro['fechaUso'])); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo date('H:i', strtotime($registro['fechaUso'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($registro['nombreUsuario']); ?></div>
                                                        <small class="text-muted">DNI: <?php echo htmlspecialchars($registro['DNI']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($registro['local_nombre']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($registro['local_rubro']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($registro['promocion_descripcion']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $estadoTexto = '';
                                                        $estadoClase = '';
                                                        switch ($registro['estado']) {
                                                            case '1':
                                                                $estadoTexto = 'Aceptado';
                                                                $estadoClase = 'estado-activo';
                                                                break;
                                                            case '2':
                                                                $estadoTexto = 'Rechazado';
                                                                $estadoClase = 'estado-inactivo';
                                                                break;
                                                            case '0':
                                                            default:
                                                                $estadoTexto = 'Pendiente';
                                                                $estadoClase = 'estado-pendiente';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge badge-estado <?php echo $estadoClase; ?>">
                                                            <?php echo $estadoTexto; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modales: Local -->
    <div class="modal fade" id="modalLocal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formLocal" class="modal-content" onsubmit="submitLocal(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLocalTitle">Nuevo Local</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_local" id="local_action">
                    <input type="hidden" name="IDlocal" id="IDlocal">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input class="form-control" name="nombre" id="local_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rubro</label>
                        <input class="form-control" name="rubro" id="local_rubro">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dueño (ID)</label>
                        <input class="form-control" name="usuarioFK" id="local_dueño" type="number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ubicación (ID)</label>
                        <input class="form-control" name="ubicacionFK" id="local_ubicacion" type="number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modales: Novedad -->
    <div class="modal fade" id="modalNovedad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formNovedad" class="modal-content" onsubmit="submitNovedad(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNovedadTitle">Nueva Novedad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_novedad" id="novedad_action">
                    <input type="hidden" name="IDnovedad" id="IDnovedad">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input class="form-control" name="titulo" id="novedad_titulo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="novedad_descripcion" rows="3"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Desde</label>
                            <input type="date" class="form-control" name="desde" id="novedad_desde">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Hasta</label>
                            <input type="date" class="form-control" name="hasta" id="novedad_hasta">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm delete modal genérico -->
    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <p id="confirmMessage">¿Confirmar eliminación?</p>
                    <div class="text-end">
                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-danger btn-sm" id="confirmDeleteBtn">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Helper ajax POST a este mismo archivo
        async function postAction(data) {
            const resp = await fetch(window.location.href, {
                method: 'POST',
                body: data
            });
            return resp.json();
        }

        // --- LOCALES ---
        function openCreateLocal() {
            document.getElementById('modalLocalTitle').textContent = 'Nuevo Local';
            document.getElementById('local_action').value = 'create_local';
            document.getElementById('IDlocal').value = '';
            document.getElementById('local_nombre').value = '';
            document.getElementById('local_rubro').value = '';
            document.getElementById('local_dueño').value = '';
            document.getElementById('local_ubicacion').value = '';
        }

        function openEditLocal(id) {
            // cargar datos desde DOM (puedes reemplazar por una petición si hace falta)
            const row = document.getElementById('local-row-' + id);
            const nombre = row.querySelector('.fw-medium').textContent.trim();
            const rubro = row.querySelector('small') ? row.querySelector('small').textContent.trim() : '';
            document.getElementById('modalLocalTitle').textContent = 'Editar Local';
            document.getElementById('local_action').value = 'update_local';
            document.getElementById('IDlocal').value = id;
            document.getElementById('local_nombre').value = nombre;
            document.getElementById('local_rubro').value = rubro;
            // dueño/ubicacion no visibles en tabla: dejar manual o mejorar con fetch
            var modal = new bootstrap.Modal(document.getElementById('modalLocal'));
            modal.show();
        }

        async function submitLocal(e) {
            e.preventDefault();
            const form = document.getElementById('formLocal');
            const data = new FormData(form);
            const json = await postAction(data);
            if (json.success) {
                location.reload();
            } else {
                alert(json.error || 'Error al guardar local');
            }
        }

        function confirmDeleteLocal(id) {
            const btn = document.getElementById('confirmDeleteBtn');
            document.getElementById('confirmMessage').textContent = '¿Eliminar local #' + id + '?';
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
            btn.onclick = async () => {
                const fd = new FormData();
                fd.append('action','delete_local');
                fd.append('IDlocal', id);
                const res = await postAction(fd);
                if (res.success) location.reload();
                else alert(res.error || 'Error al eliminar');
            };
            modal.show();
        }

        // --- SOLICITUD / VALIDACIONES / PROMOCIONES ---
        async function decideSolicitud(id, approve) {
            if (!confirm(approve ? 'Aprobar solicitud?' : 'Rechazar solicitud?')) return;
            const fd = new FormData();
            fd.append('action','decide_solicitud');
            fd.append('IDsolicitud', id);
            fd.append('approve', approve ? '1' : '0');
            const res = await postAction(fd);
            if (res.success) location.reload();
            else alert(res.error || 'Error');
        }

        async function decidePromocion(id, approve) {
            if (!confirm(approve ? 'Aprobar promoción?' : 'Rechazar promoción?')) return;
            // si el endpoint para promociones es el mismo 'decide_solicitud' cambia a la acción correcta.
            const fd = new FormData();
            fd.append('action','decide_solicitud'); // si quieres otra acción, ajusta en PHP
            fd.append('IDsolicitud', id);
            fd.append('approve', approve ? '1' : '0');
            const res = await postAction(fd);
            if (res.success) location.reload();
            else alert(res.error || 'Error');
        }

        // --- NOVEDADES ---
        function openCreateNovedad() {
            document.getElementById('modalNovedadTitle').textContent = 'Nueva Novedad';
            document.getElementById('novedad_action').value = 'create_novedad';
            document.getElementById('IDnovedad').value = '';
            document.getElementById('novedad_titulo').value = '';
            document.getElementById('novedad_descripcion').value = '';
            document.getElementById('novedad_desde').value = '';
            document.getElementById('novedad_hasta').value = '';
        }

        function openEditNovedad(id) {
            // Para simplicidad reutilizo valores existentes en DOM; si no están, pedir por fetch
            const row = document.getElementById('novedad-' + id);
            const titulo = row.querySelector('td:first-child') ? row.querySelector('td:first-child').textContent.trim() : '';
            document.getElementById('modalNovedadTitle').textContent = 'Editar Novedad';
            document.getElementById('novedad_action').value = 'update_novedad';
            document.getElementById('IDnovedad').value = id;
            document.getElementById('novedad_titulo').value = titulo;
            var modal = new bootstrap.Modal(document.getElementById('modalNovedad'));
            modal.show();
        }

        async function submitNovedad(e) {
            e.preventDefault();
            const form = document.getElementById('formNovedad');
            const data = new FormData(form);
            const res = await postAction(data);
            if (res.success) location.reload();
            else alert(res.error || 'Error al guardar novedad');
        }

        function confirmDeleteNovedad(id) {
            const btn = document.getElementById('confirmDeleteBtn');
            document.getElementById('confirmMessage').textContent = '¿Eliminar novedad #' + id + '?';
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
            btn.onclick = async () => {
                const fd = new FormData();
                fd.append('action','delete_novedad');
                fd.append('IDnovedad', id);
                const res = await postAction(fd);
                if (res.success) location.reload();
                else alert(res.error || 'Error al eliminar');
            };
            modal.show();
        }
    </script>
</body>
</html>
