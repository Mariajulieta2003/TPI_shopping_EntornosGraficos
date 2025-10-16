<?php
// Model/ProcesarTienda.php
// Inserta en tabla `solicitud` y devuelve el ID insertado.
// Requiere: Model/conexion.php con getConnection(): PDO

include_once(__DIR__ . "/conexion.php");

function saveStoreRequest(array $data) {
    $pdo = getConnection();
    // Aseguramos excepciones para poder capturar fallos reales
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // IMPORTANTE:
    //  - Columna en BD: `contraseña` (con ñ). La dejamos entre backticks.
    //  - Parámetro nombrado: :contrasena (sin ñ) para evitar problemas de encoding.
    $sql = "INSERT INTO solicitud
              (`nombre`, `email`, `contraseña`, `telefono`, `sexo`, `dni`, `cuil`, `rubro`, `nombreLocal`, `ubicacion`)
            VALUES
              (:nombre, :email, :contrasena, :telefono, :sexo, :dni, :cuil, :rubro, :nombreLocal, :ubicacion)";

    $stmt = $pdo->prepare($sql);

    // Hash seguro de la contraseña (si ya te viene hasheada, quitá esta línea)
    $hash = password_hash((string)($data['contrasena'] ?? ''), PASSWORD_DEFAULT);

    $ok = $stmt->execute([
        'nombre'       => (string)$data['nombre'],
        'email'        => (string)$data['email'],
        'contrasena'   => $hash,
        'telefono'     => ($data['telefono'] === '' ? null : (string)$data['telefono']),
        'sexo'         => ($data['sexo'] === '' ? null : (string)$data['sexo']),
        'dni'          => (string)$data['dni'],
        'cuil'         => (string)$data['cuil'],
        'rubro'        => (string)$data['rubro'],
        'nombreLocal'  => (string)$data['nombreLocal'],
        'ubicacion'    => (string)$data['ubicacion'],
    ]);

    if (!$ok) {
        // Ejecutó pero no OK → devolvemos estructura de error de negocio
        return ['ok' => false, 'message' => 'No se pudo insertar la solicitud.', 'errors' => ['general' => 'Fallo al insertar.']];
    }

    $id = (int)$pdo->lastInsertId();
    // Si tu tabla no es AUTO_INCREMENT, podrías retornar true; pero es mejor devolver el ID:
    return $id;
}





function getUbicaciones(): array {
    try {
        $pdo = getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT IDubicacion, nombre
                  FROM ubicacion
                 WHERE estado = 0
              ORDER BY nombre ASC";
        $stmt = $pdo->query($sql);

        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    } catch (Throwable $e) {
        error_log('getUbicaciones: '.$e->getMessage());
        return []; 
    }
}
