<?php
require_once '../Model/conexion.php';
function getNovedades() {
    $pdo = getConnection();
    $query = "
        SELECT 
            n.*,
            CASE 
                WHEN n.usuarioHabilitado = 'Inicial' THEN 'Todos los clientes'
                WHEN n.usuarioHabilitado = 'Medium' THEN 'Medium y Premium'
                WHEN n.usuarioHabilitado = 'Premium' THEN 'Solo Premium'
                ELSE n.usuarioHabilitado
            END as audiencia_descripcion
        FROM novedad n
        ORDER BY n.desde DESC, n.IDnovedad DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNovedadById($id) {
    $pdo = getConnection();
    $query = "SELECT * FROM novedad WHERE IDnovedad = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function crearNovedad($datos) {
    $pdo = getConnection();
    $query = "
        INSERT INTO novedad 
        (desde, hasta, usuarioHabilitado, descripcion, cabecera, cuerpo, imagen) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([
        $datos['desde'],
        $datos['hasta'],
        $datos['usuarioHabilitado'],
        $datos['descripcion'],
        $datos['cabecera'],
        $datos['cuerpo'],
        null 
    ]);
}

function actualizarNovedad($id, $datos) {
    $pdo = getConnection();
    $query = "
        UPDATE novedad 
        SET desde = ?, hasta = ?, usuarioHabilitado = ?, descripcion = ?, cabecera = ?, cuerpo = ?
        WHERE IDnovedad = ?
    ";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([
        $datos['desde'],
        $datos['hasta'],
        $datos['usuarioHabilitado'],
        $datos['descripcion'],
        $datos['cabecera'],
        $datos['cuerpo'],
        $id
    ]);
}

function eliminarNovedad($id) {
    $pdo = getConnection();
    $query = "DELETE FROM novedad WHERE IDnovedad = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([$id]);
}

function getEstadisticasNovedades() {
    $pdo = getConnection();
    
    $stats = [];
    
    $query = "SELECT COUNT(*) as total FROM novedad";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM novedad WHERE hasta >= CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM novedad WHERE hasta < CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['expiradas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT usuarioHabilitado, COUNT(*) as cantidad FROM novedad GROUP BY usuarioHabilitado";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['categorias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $stats;
}
?>