<?php
require_once '../Model/conexion.php';



// Funciones para la gestión de locales
function getLocalesCompletos() {
    $pdo = getConnection();
    $query = "
        SELECT 
            l.*, 
            u.nombreUsuario as dueño_nombre,
            u.email as dueño_email,
            ub.nombre as ubicacion_nombre,
            ub.Descripcion as ubicacion_descripcion,
            COUNT(p.IDpromocion) as total_promociones
        FROM local l 
        LEFT JOIN usuario u ON l.usuarioFK = u.IDusuario 
        LEFT JOIN ubicacion ub ON l.ubicacionFK = ub.IDubicacion
        LEFT JOIN promocion p ON l.IDlocal = p.localFk AND p.estado = 1
        GROUP BY l.IDlocal
        ORDER BY l.nombre
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUbicaciones() {
    $pdo = getConnection();
    $query = "SELECT * FROM ubicacion WHERE estado = 0 ORDER BY nombre";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getComerciantes() {
    $pdo = getConnection();
    $query = "SELECT IDusuario, nombreUsuario, email FROM usuario WHERE tipoFK = 2 AND estado = 1 ORDER BY nombreUsuario";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLocalById($id) {
    $pdo = getConnection();
    $query = "SELECT * FROM local WHERE IDlocal = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function crearLocal($datos) {
    $pdo = getConnection();
    
    // Generar código único para el local
    $codigo = 'LOCAL_' . strtoupper(uniqid());
    
    $query = "INSERT INTO local (nombre, rubro, usuarioFK, ubicacionFK, codigo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([
        $datos['nombre'],
        $datos['rubro'],
        $datos['usuarioFK'],
        $datos['ubicacionFK'],
        $codigo
    ]);
}

function actualizarLocal($id, $datos) {
    $pdo = getConnection();
    $query = "UPDATE local SET nombre = ?, rubro = ?, usuarioFK = ?, ubicacionFK = ? WHERE IDlocal = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([
        $datos['nombre'],
        $datos['rubro'],
        $datos['usuarioFK'],
        $datos['ubicacionFK'],
        $id
    ]);
}

function eliminarLocal($id) {
    $pdo = getConnection();
    
    // Verificar si hay promociones activas
    $queryCheck = "SELECT COUNT(*) as total FROM promocion WHERE localFk = ? AND estado = 1";
    $stmtCheck = $pdo->prepare($queryCheck);
    $stmtCheck->execute([$id]);
    $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        return ['success' => false, 'message' => 'No se puede eliminar el local porque tiene promociones activas.'];
    }
    
    // Eliminar local
    $query = "DELETE FROM local WHERE IDlocal = ?";
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([$id]);
    
    return ['success' => $success, 'message' => $success ? 'Local eliminado correctamente.' : 'Error al eliminar el local.'];
}
?>