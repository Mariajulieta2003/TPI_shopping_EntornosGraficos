<?php
require_once '../Model/conexion.php';




function getLocalPorUsuario($idUsuario) {
    $pdo = getConnection();
    $query = "SELECT IDlocal, nombre, rubro FROM local WHERE usuarioFK = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idUsuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function getHistorialUsos($idLocal, $filtros = []) {
    $pdo = getConnection();
    
    $whereConditions = ["p.localFk = ?"];
    $params = [$idLocal];
    
    
    if (!empty($filtros['fecha_desde'])) {
        $whereConditions[] = "up.fechaUso >= ?";
        $params[] = $filtros['fecha_desde'];
    }
    
  
    if (!empty($filtros['fecha_hasta'])) {
        $whereConditions[] = "up.fechaUso <= ?";
        $params[] = $filtros['fecha_hasta'];
    }
    
 
    if (isset($filtros['estado']) && $filtros['estado'] !== '') {
        $whereConditions[] = "up.estado = ?";
        $params[] = $filtros['estado'];
    }
    

    if (!empty($filtros['categoria_usuario'])) {
        $whereConditions[] = "u.categoriaFK = ?";
        $params[] = $filtros['categoria_usuario'];
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    $query = "
        SELECT 
            up.fechaUso,
            up.estado,
            u.nombreUsuario,
            u.DNI,
            u.email,
            c.nombre as categoria,
            p.descripcion as promocion_descripcion,
            p.categoriaHabilitada,
            p.desde as promocion_desde,
            p.hasta as promocion_hasta
        FROM usopromocion up
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion
        INNER JOIN usuario u ON up.usuarioFk = u.IDusuario
        INNER JOIN categoria c ON u.categoriaFK = c.IDcategoria
        WHERE $whereClause
        ORDER BY up.fechaUso DESC, up.estado ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getCategoriasUsuarios() {
    $pdo = getConnection();
    $query = "SELECT IDcategoria, nombre FROM categoria";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getEstadisticasHistorial($idLocal, $filtros = []) {
    $historial = getHistorialUsos($idLocal, $filtros);
    
    $total = count($historial);
    $aceptados = 0;
    $rechazados = 0;
    $pendientes = 0;
    
    foreach ($historial as $uso) {
        switch ($uso['estado']) {
            case '1': $aceptados++; break;
            case '2': $rechazados++; break;
            case '0': $pendientes++; break;
        }
    }
    
    return [
        'total' => $total,
        'aceptados' => $aceptados,
        'rechazados' => $rechazados,
        'pendientes' => $pendientes,
        'tasa_aceptacion' => $total > 0 ? round(($aceptados / $total) * 100, 1) : 0
    ];
}
?>