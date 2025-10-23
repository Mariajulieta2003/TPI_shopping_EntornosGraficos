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

                    <div class="row">
                        <!-- Locales Recientes -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Locales Registrados</h5>
                                    <a href="./GestionLocales.php" class="btn btn-sm btn-primary">Ver Todos</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($locales)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-shop" style="font-size: 2rem; color: var(--muted);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay locales registrados</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Dueño</th>
                                                        <th>Ubicación</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($locales, 0, 5) as $local): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($local['nombre']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($local['rubro']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($local['dueño'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($local['ubicacion_nombre'] ?? 'N/A'); ?></td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-primary action-btn" title="Editar">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger action-btn" title="Eliminar">
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

                        <!-- Solicitudes Pendientes -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Solicitudes Pendientes</h5>
                                    <span class="badge bg-warning"><?php echo count($solicitudesPendientes); ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($solicitudesPendientes)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--success);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay solicitudes pendientes</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach (array_slice($solicitudesPendientes, 0, 5) as $solicitud): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($solicitud['nombre']); ?></h6>
                                                        <p class="mb-1 small text-muted"><?php echo htmlspecialchars($solicitud['email']); ?></p>
                                                        <small class="text-muted">Local: <?php echo htmlspecialchars($solicitud['nombreLocal']); ?></small>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-success" title="Aprobar">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" title="Rechazar">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function limpiarFiltros() {
            document.getElementById('fecha_desde').value = '';
            document.getElementById('fecha_hasta').value = '';
            document.getElementById('local_id').value = '';
            document.getElementById('filtrosForm').submit();
        }

        // Validación de fechas
        document.getElementById('fecha_desde').addEventListener('change', function() {
            const fechaHasta = document.getElementById('fecha_hasta');
            if (this.value && fechaHasta.value && this.value > fechaHasta.value) {
                alert('La fecha "desde" no puede ser posterior a la fecha "hasta"');
                this.value = '';
            }
        });

        document.getElementById('fecha_hasta').addEventListener('change', function() {
            const fechaDesde = document.getElementById('fecha_desde');
            if (this.value && fechaDesde.value && this.value < fechaDesde.value) {
                alert('La fecha "hasta" no puede ser anterior a la fecha "desde"');
                this.value = '';
            }
        });
    </script>
</body>
</html>