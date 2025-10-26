<?php
session_start();
require_once '../Model/conexion.php';

// Funciones para la gestión de solicitudes
function getSolicitudesPendientes() {
    $pdo = getConnection();
    $query = "
        SELECT 
            s.*,
            u.nombre as ubicacion_nombre,
            u.Descripcion as ubicacion_descripcion
        FROM solicitud s
        LEFT JOIN ubicacion u ON s.ubicacion = u.IDubicacion
        WHERE s.estado = 0
        ORDER BY s.IDsolicitud DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesAprobadas() {
    $pdo = getConnection();
    $query = "
        SELECT 
            s.*,
            u.nombre as ubicacion_nombre
        FROM solicitud s
        LEFT JOIN ubicacion u ON s.ubicacion = u.IDubicacion
        WHERE s.estado = 1
        ORDER BY s.IDsolicitud DESC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesRechazadas() {
    $pdo = getConnection();
    $query = "
        SELECT 
            s.*,
            u.nombre as ubicacion_nombre
        FROM solicitud s
        LEFT JOIN ubicacion u ON s.ubicacion = u.IDubicacion
        WHERE s.estado = 2
        ORDER BY s.IDsolicitud DESC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function crearOActualizarUsuarioComerciante($datosSolicitud) {
    $pdo = getConnection();
    
    // Verificar si el usuario existe por DNI
    $queryCheck = "SELECT IDusuario, tipoFK, nombreUsuario, email FROM usuario WHERE DNI = ?";
    $stmtCheck = $pdo->prepare($queryCheck);
    $stmtCheck->execute([$datosSolicitud['dni']]);
    $usuarioExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioExistente) {
        // Usuario existe, actualizar a comerciante (rol 2)
        $queryUpdate = "UPDATE usuario SET tipoFK = 2 WHERE IDusuario = ?";
        $stmtUpdate = $pdo->prepare($queryUpdate);
        $stmtUpdate->execute([$usuarioExistente['IDusuario']]);
        
        return [
            'id' => $usuarioExistente['IDusuario'],
            'accion' => 'actualizado',
            'email' => $usuarioExistente['email'],
            'nombreUsuario' => $usuarioExistente['nombreUsuario']
        ];
    } else {
        // Crear nuevo usuario comerciante
        $query = "
            INSERT INTO usuario 
            (nombreUsuario, email, clave, telefono, Sexo, tipoFK, categoriaFK, estado, DNI) 
            VALUES (?, ?, ?, ?, ?, 2, 1, 1, ?)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $datosSolicitud['nombre'],
            $datosSolicitud['email'],
            $datosSolicitud['contraseña'],
            $datosSolicitud['telefono'],
            $datosSolicitud['sexo'],
            $datosSolicitud['dni']
        ]);
        
        return [
            'id' => $pdo->lastInsertId(),
            'accion' => 'creado',
            'email' => $datosSolicitud['email'],
            'nombreUsuario' => $datosSolicitud['nombre']
        ];
    }
}

function crearLocalDesdeSolicitud($datosSolicitud, $usuarioId) {
    $pdo = getConnection();
    
    // Verificar que la ubicación existe
    $queryCheckUbicacion = "SELECT IDubicacion FROM ubicacion WHERE IDubicacion = ? AND estado = 0";
    $stmtCheck = $pdo->prepare($queryCheckUbicacion);
    $stmtCheck->execute([$datosSolicitud['ubicacion']]);
    $ubicacion = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$ubicacion) {
        throw new Exception("La ubicación seleccionada no existe o no está disponible");
    }
    
    // Generar código único para el local
    $codigo = 'LOCAL_' . strtoupper(uniqid());
    
    $query = "
        INSERT INTO local 
        (nombre, rubro, usuarioFK, ubicacionFK, codigo) 
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($query);
    $resultado = $stmt->execute([
        $datosSolicitud['nombreLocal'],
        $datosSolicitud['rubro'],
        $usuarioId,
        $datosSolicitud['ubicacion'],
        $codigo
    ]);
    
    if ($resultado) {
        return $codigo;
    }
    
    return false;
}

function actualizarEstadoSolicitud($idSolicitud, $estado) {
    $pdo = getConnection();
    $query = "UPDATE solicitud SET estado = ? WHERE IDsolicitud = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([$estado, $idSolicitud]);
}

function enviarEmailAprobacion($solicitud, $localCodigo, $credenciales) {
    $destinatario = $solicitud['email'];
    $asunto = "✅ Tu solicitud ha sido aprobada - ShoppingGenerico";
    
    $mensajeAccion = ($credenciales['accion'] == 'actualizado') 
        ? "Tu cuenta existente ha sido actualizada a comerciante." 
        : "Se ha creado una nueva cuenta de comerciante para ti.";
    
    $mensaje = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4A3BC7; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #4A3BC7; }
            .credenciales { background: #e8f4fd; border-left: 4px solid #2196F3; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .advertencia { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Felicidades, {$solicitud['nombre']}!</h1>
                <p>Tu solicitud para abrir un local ha sido aprobada</p>
            </div>
            <div class='content'>
                <p>Estamos encantados de informarte que tu solicitud para abrir un local en ShoppingGenerico ha sido <strong>aprobada</strong>.</p>
                <p>{$mensajeAccion}</p>
                
                <div class='info-box credenciales'>
                    <h3>🔐 Tus Credenciales de Acceso:</h3>
                    <p><strong>Email:</strong> {$credenciales['email']}</p>
                    <p><strong>Contraseña:</strong> La que ingresaste en tu solicitud</p>
                    <p><strong>Rol:</strong> Comerciante</p>
                </div>
                
                <div class='advertencia'>
                    <strong>⚠️ Importante:</strong> Guarda esta información en un lugar seguro.
                </div>
                
                <div class='info-box'>
                    <h3>🏪 Información de tu local:</h3>
                    <p><strong>Nombre del local:</strong> {$solicitud['nombreLocal']}</p>
                    <p><strong>Rubro:</strong> {$solicitud['rubro']}</p>
                    <p><strong>Código único del local:</strong> <code>{$localCodigo}</code></p>
                </div>
                
                <div class='info-box'>
                    <h3>📋 Próximos pasos:</h3>
                    <ol>
                        <li>Accede a tu panel de comerciante usando las credenciales anteriores</li>
                        <li>Configura tus promociones y horarios</li>
                        <li>Comienza a atraer clientes con ofertas especiales</li>
                    </ol>
                </div>
                
                <p><strong>🔗 Accede a tu cuenta:</strong> <a href='http://localhost/ShoppingGenerico/login.php'>Iniciar Sesión</a></p>
            </div>
            <div class='footer'>
                <p>Saludos cordiales,<br>El equipo de ShoppingGenerico</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmailRechazo($solicitud) {
    $destinatario = $solicitud['email'];
    $asunto = "❌ Actualización sobre tu solicitud - ShoppingGenerico";
    
    $mensaje = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Actualización sobre tu solicitud</h1>
            </div>
            <div class='content'>
                <p>Hola {$solicitud['nombre']},</p>
                <p>Lamentamos informarte que después de revisar tu solicitud, hemos decidido <strong>no proceder</strong> con la apertura de tu local en este momento.</p>
                
                <p>Esto puede deberse a varias razones, como:</p>
                <ul>
                    <li>Capacidad limitada en la ubicación solicitada</li>
                    <li>Rubro ya cubierto en nuestra oferta comercial</li>
                    <li>Necesidad de documentación adicional</li>
                </ul>
                
                <p>Si deseas más información sobre esta decisión o quieres presentar una nueva solicitud en el futuro, no dudes en contactarnos.</p>
                
                <p>Agradecemos tu interés en formar parte de ShoppingGenerico.</p>
            </div>
            <div class='footer'>
                <p>Saludos cordiales,<br>El equipo de ShoppingGenerico</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmail($destinatario, $asunto, $mensaje) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@shoppinggenerico.com" . "\r\n";
    $headers .= "Reply-To: admin@shoppinggenerico.com" . "\r\n";
    
    return mail($destinatario, $asunto, $mensaje, $headers);
}

function getEstadisticasSolicitudes() {
    $pdo = getConnection();
    
    $stats = [];
    
    // Solicitudes pendientes
    $query = "SELECT COUNT(*) as total FROM solicitud WHERE estado = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Solicitudes aprobadas (total)
    $query = "SELECT COUNT(*) as total FROM solicitud WHERE estado = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['aprobadas_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Solicitudes rechazadas (total)
    $query = "SELECT COUNT(*) as total FROM solicitud WHERE estado = 2";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['rechazadas_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tasa de aprobación
    $total_revisadas = $stats['aprobadas_total'] + $stats['rechazadas_total'];
    $stats['tasa_aprobacion'] = $total_revisadas > 0 ? round(($stats['aprobadas_total'] / $total_revisadas) * 100, 1) : 0;
    
    return $stats;
}

// Procesar acciones
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idSolicitud = $_POST['id_solicitud'] ?? 0;
    
    // Obtener datos de la solicitud
    $pdo = getConnection();
    $query = "SELECT * FROM solicitud WHERE IDsolicitud = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idSolicitud]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$solicitud) {
        $mensaje = 'Solicitud no encontrada.';
        $tipoMensaje = 'danger';
    } else {
        switch ($action) {
            case 'aprobar':
                try {
                    $pdo->beginTransaction();
                    
                    // 1. Crear o actualizar usuario comerciante
                    $resultadoUsuario = crearOActualizarUsuarioComerciante($solicitud);
                    
                    // 2. Crear local
                    $localCodigo = crearLocalDesdeSolicitud($solicitud, $resultadoUsuario['id']);
                    
                    if ($localCodigo) {
                        // 3. Actualizar estado de la solicitud
                        actualizarEstadoSolicitud($idSolicitud, 1);
                        
                        // 4. Enviar email de aprobación con credenciales
                        $emailEnviado = enviarEmailAprobacion($solicitud, $localCodigo, $resultadoUsuario);
                        
                        $pdo->commit();
                        
                        $mensaje = "Solicitud aprobada exitosamente. ";
                        $mensaje .= "Usuario {$resultadoUsuario['accion']} como comerciante. ";
                        $mensaje .= $emailEnviado ? "Se envió notificación con credenciales." : "Error al enviar email, pero el proceso se completó.";
                        $tipoMensaje = 'success';
                    } else {
                        throw new Exception('Error al crear el local.');
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $mensaje = 'Error durante el proceso de aprobación: ' . $e->getMessage();
                    $tipoMensaje = 'danger';
                }
                break;
                
            case 'rechazar':
                if (actualizarEstadoSolicitud($idSolicitud, 2)) {
                    // Enviar email de rechazo
                    $emailEnviado = enviarEmailRechazo($solicitud);
                    
                    $mensaje = 'Solicitud rechazada exitosamente. Se envió notificación al solicitante.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al rechazar la solicitud.';
                    $tipoMensaje = 'danger';
                }
                break;
        }
    }
    
    // Redirigir para evitar reenvío del formulario
    header('Location: AprobarSolicitudes.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipoMensaje);
    exit;
}

// Manejar mensajes de redirección
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

$solicitudesPendientes = getSolicitudesPendientes();
$solicitudesAprobadas = getSolicitudesAprobadas();
$solicitudesRechazadas = getSolicitudesRechazadas();
$estadisticas = getEstadisticasSolicitudes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar Solicitudes - ShoppingGenerico</title>
    
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

        .table-hover tbody tr:hover {
            background-color: rgba(var(--primary-rgb), 0.04);
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .estado-pendiente { background-color: var(--warning); color: #000; }
        .estado-aprobado { background-color: var(--success); color: #fff; }
        .estado-rechazado { background-color: var(--danger); color: #fff; }

        .solicitud-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
        }

        .solicitud-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .solicitud-card.pendiente {
            border-left: 4px solid var(--warning);
        }

        .solicitud-card.aprobada {
            border-left: 4px solid var(--success);
        }

        .solicitud-card.rechazada {
            border-left: 4px solid var(--danger);
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
                <strong>ShoppingUTN- Administrador</strong>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <strong class="me-2"><?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Admin'); ?></strong>
                        <span class="badge bg-light text-primary">Administrador</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="./DashboardAdmin.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="./GestionLocales.php">Gestión de Locales</a></li>
                        <li><a class="dropdown-item active" href="./AprobarSolicitudes.php">Aprobar Solicitudes</a></li>
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
                            <a class="nav-link" href="./DashboardAdministrador.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./GestionLocales.php">
                                <i class="bi bi-shop me-2"></i>Gestión de Locales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="./AprobarSolicitudes.php">
                                <i class="bi bi-clipboard-check me-2"></i>Aprobar Solicitudes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./AprobarPromociones.php">
                                <i class="bi bi-tag me-2"></i>Aprobar Promociones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="./GestionNovedades.php">
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
                                <i class="bi bi-clipboard-check me-2" style="color: var(--primary);"></i>
                                Aprobar Solicitudes de Locales
                            </h1>
                            <p class="text-muted mb-0">Revisa y gestiona las solicitudes de apertura de locales</p>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Estadísticas -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Pendientes</h6>
                                        <div class="h4 mb-0 text-warning"><?php echo $estadisticas['pendientes']; ?></div>
                                    </div>
                                    <i class="bi bi-clock-history" style="font-size: 1.5rem; color: var(--warning);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Aprobadas</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticas['aprobadas_total']; ?></div>
                                    </div>
                                    <i class="bi bi-check-circle" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Rechazadas</h6>
                                        <div class="h4 mb-0 text-danger"><?php echo $estadisticas['rechazadas_total']; ?></div>
                                    </div>
                                    <i class="bi bi-x-circle" style="font-size: 1.5rem; color: var(--danger);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Tasa Aprobación</h6>
                                        <div class="h4 mb-0 text-info"><?php echo $estadisticas['tasa_aprobacion']; ?>%</div>
                                    </div>
                                    <i class="bi bi-percent" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestañas -->
                    <ul class="nav nav-tabs mb-4" id="solicitudesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                                Pendientes
                                <span class="badge bg-warning ms-1"><?php echo count($solicitudesPendientes); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="aprobadas-tab" data-bs-toggle="tab" data-bs-target="#aprobadas" type="button" role="tab">
                                Aprobadas
                                <span class="badge bg-success ms-1"><?php echo count($solicitudesAprobadas); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rechazadas-tab" data-bs-toggle="tab" data-bs-target="#rechazadas" type="button" role="tab">
                                Rechazadas
                                <span class="badge bg-danger ms-1"><?php echo count($solicitudesRechazadas); ?></span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="solicitudesTabContent">
                        <!-- Pestaña Pendientes -->
                        <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                            <?php if (empty($solicitudesPendientes)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle" style="font-size: 3rem; color: var(--success);"></i>
                                    <h5 class="mt-3 text-muted">No hay solicitudes pendientes</h5>
                                    <p class="text-muted">Todas las solicitudes han sido revisadas.</p>
                                </div>
                            <?php else: ?>
                                <!-- Lista de solicitudes pendientes -->
                                <div class="row g-3">
                                    <?php foreach ($solicitudesPendientes as $solicitud): ?>
                                        <div class="col-12 col-lg-6">
                                            <div class="solicitud-card pendiente p-4">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h6 class="mb-0">Solicitud #<?php echo $solicitud['IDsolicitud']; ?></h6>
                                                    <span class="badge estado-pendiente">Pendiente</span>
                                                </div>
                                                
                                                <!-- Información del solicitante -->
                                                <div class="mb-3">
                                                    <h6 class="text-primary">Información del Solicitante</h6>
                                                    <div class="row small text-muted">
                                                        <div class="col-6">
                                                            <strong>Nombre:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['nombre']); ?>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Email:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['email']); ?>
                                                        </div>
                                                        <div class="col-6 mt-2">
                                                            <strong>DNI:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['dni']); ?>
                                                        </div>
                                                        <div class="col-6 mt-2">
                                                            <strong>Teléfono:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['telefono']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Información del local -->
                                                <div class="mb-3">
                                                    <h6 class="text-primary">Información del Local</h6>
                                                    <div class="row small text-muted">
                                                        <div class="col-6">
                                                            <strong>Nombre del local:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['nombreLocal']); ?>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Rubro:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['rubro']); ?>
                                                        </div>
                                                        <div class="col-12 mt-2">
                                                            <strong>Ubicación solicitada:</strong><br>
                                                            <?php echo htmlspecialchars($solicitud['ubicacion_nombre']); ?>
                                                            <?php if ($solicitud['ubicacion_descripcion']): ?>
                                                                <br><small><?php echo htmlspecialchars($solicitud['ubicacion_descripcion']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="aprobar">
                                                        <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['IDsolicitud']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" 
                                                                onclick="return confirm('¿Estás seguro de que deseas APROBAR esta solicitud? Se creará un nuevo local.')">
                                                            <i class="bi bi-check-lg me-1"></i>Aprobar
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="rechazar">
                                                        <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['IDsolicitud']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('¿Estás seguro de que deseas RECHAZAR esta solicitud?')">
                                                            <i class="bi bi-x-lg me-1"></i>Rechazar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pestaña Aprobadas -->
                        <div class="tab-pane fade" id="aprobadas" role="tabpanel">
                            <?php if (empty($solicitudesAprobadas)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No hay solicitudes aprobadas</h5>
                                    <p class="text-muted">Las solicitudes aprobadas aparecerán aquí.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Solicitante</th>
                                                <th>Local</th>
                                                <th>CUIT/CUIL</th>
                                                <th>Ubicación</th>
                                                <th>Teléfono</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($solicitudesAprobadas as $solicitud): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($solicitud['nombre']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($solicitud['email']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($solicitud['nombreLocal']); ?></td>
                                                    <td><?php echo htmlspecialchars($solicitud['cuil']); ?></td>
                                                    <td><?php echo htmlspecialchars($solicitud['ubicacion_nombre']); ?></td>
                                                    <td>
                                                        <code><?php echo htmlspecialchars($solicitud['telefono']); ?></code>
                                                    </td>
                                                    <td>
                                                        <span class="badge estado-aprobado">Aprobada</span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pestaña Rechazadas -->
                        <div class="tab-pane fade" id="rechazadas" role="tabpanel">
                            <?php if (empty($solicitudesRechazadas)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No hay solicitudes rechazadas</h5>
                                    <p class="text-muted">Las solicitudes rechazadas aparecerán aquí.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Solicitante</th>
                                                <th>Local Solicitado</th>
                                                <th>Rubro</th>
                                                <th>Ubicación</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($solicitudesRechazadas as $solicitud): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($solicitud['nombre']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($solicitud['email']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($solicitud['nombreLocal']); ?></td>
                                                    <td><?php echo htmlspecialchars($solicitud['rubro']); ?></td>
                                                    <td><?php echo htmlspecialchars($solicitud['ubicacion_nombre']); ?></td>
                                                    <td>
                                                        <span class="badge estado-rechazado">Rechazada</span>
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
        // Cerrar alertas automáticamente después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>