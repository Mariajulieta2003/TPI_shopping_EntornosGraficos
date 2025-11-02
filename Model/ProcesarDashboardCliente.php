<?php
require_once 'conexion.php';

function getPromocionesUsadasPorCliente($usuario_id) {
    $pdo = getConnection();
    $query = "
        SELECT COUNT(*) as total 
        FROM usopromocion 
        WHERE usuarioFk = ? 
        AND estado = 1 
        AND fechaUso >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function actualizarCategoriaUsuario($usuario_id) {
    $pdo = getConnection();
    
    // Obtener número de promociones usadas en los últimos 6 meses
    $promociones_usadas = getPromocionesUsadasPorCliente($usuario_id);
    
    // Determinar nueva categoría
    if ($promociones_usadas < 5) {
        $nueva_categoria = 'Inicial';
    } elseif ($promociones_usadas >= 5 && $promociones_usadas <= 12) {
        $nueva_categoria = 'Medium';
    } else {
        $nueva_categoria = 'Premium';
    }
    
    // Obtener ID de la categoría
    $query_categoria = "SELECT IDcategoria FROM categoria WHERE nombre = ?";
    $stmt_categoria = $pdo->prepare($query_categoria);
    $stmt_categoria->execute([$nueva_categoria]);
    $categoria = $stmt_categoria->fetch(PDO::FETCH_ASSOC);
    
    if ($categoria) {
        $categoria_id = $categoria['IDcategoria'];
        
        $query_actualizar = "UPDATE usuario SET categoriaFK = ? WHERE IDusuario = ?";
        $stmt_actualizar = $pdo->prepare($query_actualizar);
        $stmt_actualizar->execute([$categoria_id, $usuario_id]);
        
        return [
            'nueva_categoria' => $nueva_categoria,
            'promociones_usadas' => $promociones_usadas,
            'categoria_id' => $categoria_id
        ];
    }
    
    return false;
}

function getInfoProgresoCategoria($usuario_id) {
    $promociones_usadas = getPromocionesUsadasPorCliente($usuario_id);
    
    if ($promociones_usadas < 5) {
        $categoria_actual = 'Inicial';
        $proximo_limite = 5;
        $proxima_categoria = 'Medium';
        $progreso = ($promociones_usadas / 5) * 100;
    } elseif ($promociones_usadas >= 5 && $promociones_usadas <= 12) {
        $categoria_actual = 'Medium';
        $proximo_limite = 12;
        $proxima_categoria = 'Premium';
        $progreso = (($promociones_usadas - 5) / (12 - 5)) * 100;
    } else {
        $categoria_actual = 'Premium';
        $proximo_limite = null;
        $proxima_categoria = null;
        $progreso = 100;
    }
    
    return [
        'categoria_actual' => $categoria_actual,
        'promociones_usadas' => $promociones_usadas,
        'proximo_limite' => $proximo_limite,
        'proxima_categoria' => $proxima_categoria,
        'progreso_porcentaje' => $progreso,
        'restantes' => $proximo_limite ? $proximo_limite - $promociones_usadas : 0
    ];
}

function getPromocionesDisponiblesPorCategoria($categoria) {
    $pdo = getConnection();
    $query = "
        SELECT COUNT(*) as total 
        FROM promocion 
        WHERE categoriaHabilitada = ? 
        AND estado = 1 
        AND hasta >= CURDATE()
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$categoria]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getNovedadesPorCategoria($categoria) {
    $pdo = getConnection();
    $query = "
        SELECT COUNT(*) as total 
        FROM novedad 
        WHERE usuarioHabilitado = ? 
        AND hasta >= CURDATE()
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$categoria]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPromocionesPorCategoria($categoria, $usuario_id) {
    $pdo = getConnection();
    $query = "
        SELECT 
            p.IDpromocion,
            p.descripcion,
            p.desde,
            p.hasta,
            p.categoriaHabilitada,
            p.dia,
            l.nombre as local_nombre,
            l.codigo,
            u.nombre as ubicacion_nombre
        FROM promocion p
        INNER JOIN local l ON p.localFk = l.IDlocal
        LEFT JOIN ubicacion u ON l.ubicacionFK = u.IDubicacion
        WHERE p.categoriaHabilitada = ? 
        AND p.estado = 0
        AND p.hasta >= CURDATE()
        AND p.IDpromocion NOT IN (
            SELECT promoFK 
            FROM usopromocion 
            WHERE usuarioFk = ? AND estado IN (0, 1)
        )
        ORDER BY p.hasta ASC
        LIMIT 6
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$categoria, $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHistorialUsoCliente($usuario_id, $limite = 8) {
    $pdo = getConnection();
    $query = "
        SELECT 
            up.fechaUso,
            up.estado,
            p.descripcion as descripcion_promo,
            p.categoriaHabilitada,
            l.nombre as nombre_local
        FROM usopromocion up
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion
        INNER JOIN local l ON p.localFk = l.IDlocal
        WHERE up.usuarioFk = ?
        ORDER BY up.fechaUso DESC
        LIMIT " . intval($limite);
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNovedadesRecientesPorCategoria($categoria, $limite = 8) {
    $pdo = getConnection();
    $query = "
        SELECT 
            cabecera,
            descripcion,
            desde,
            hasta,
            usuarioHabilitado as categoriaHabilitada
        FROM novedad 
        WHERE usuarioHabilitado = ? 
        AND hasta >= CURDATE() 
        ORDER BY desde DESC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $categoria, PDO::PARAM_STR);
    $stmt->bindValue(2, (int)$limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    session_start();
    
    if ($_POST['action'] === 'solicitar' && isset($_POST['idPromocion'])) {
        $usuario_id = $_SESSION['IDusuario'] ?? 0;
        $promocion_id = $_POST['idPromocion'];
        
        $pdo = getConnection();
        
        // Verificar que la promoción existe y está disponible
        $query_verificar = "
            SELECT p.IDpromocion 
            FROM promocion p
            WHERE p.IDpromocion = ?
              AND p.estado = 1
              AND CURDATE() BETWEEN p.desde AND p.hasta
        ";
        
        $stmt = $pdo->prepare($query_verificar);
        $stmt->execute([$promocion_id]);
        $promocion_valida = $stmt->fetch();
        
        if ($promocion_valida) {
            // Verificar si ya usó esta promoción
            $query_verificar_uso = "
                SELECT * FROM usopromocion 
                WHERE usuarioFk = ? AND promoFK = ? AND estado IN (0, 1)
            ";
            $stmt_uso = $pdo->prepare($query_verificar_uso);
            $stmt_uso->execute([$usuario_id, $promocion_id]);
            
            if ($stmt_uso->fetch()) {
                echo json_encode(['ok' => false, 'msg' => 'Ya has solicitado esta promoción anteriormente.']);
            } else {
                $query_insert = "
                    INSERT INTO usopromocion (usuarioFk, promoFK, fechaUso, estado) 
                    VALUES (?, ?, CURDATE(), 0)
                ";
                $stmt_insert = $pdo->prepare($query_insert);
                
                if ($stmt_insert->execute([$usuario_id, $promocion_id])) {
                    // Actualizar categoría después de usar promoción
                    actualizarCategoriaUsuario($usuario_id);
                    
                    echo json_encode(['ok' => true, 'msg' => 'Promoción solicitada exitosamente.']);
                } else {
                    echo json_encode(['ok' => false, 'msg' => 'Error al registrar la solicitud.']);
                }
            }
        } else {
            echo json_encode(['ok' => false, 'msg' => 'La promoción no está disponible.']);
        }
    }
    exit;
}
?>