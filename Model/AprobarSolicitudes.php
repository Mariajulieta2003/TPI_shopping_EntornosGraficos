<?php

require_once '../Model/conexion.php';
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
?>