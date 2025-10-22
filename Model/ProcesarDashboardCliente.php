<?php
// ../Model/ProcesarDashboardCliente.php
// Depende de: ../Model/conexion.php que debe exponer getConnection(): PDO


include_once __DIR__ . "/conexion.php";

/**
 * Normaliza las categorías a un ranking para poder comparar "categoria o inferior".
 * Acepta equivalencias del enunciado y del dataset de ejemplo.
 */
function categoria_rank(string $categoria): int {
    $map = [
        'inicial' => 1, 'basico' => 1, 'básico' => 1,
        'medium'  => 2, 'medio'  => 2,
        'premium' => 3, 'avanzado'=> 3, 'rockstar'=> 3,
    ];
    $key = mb_strtolower(trim($categoria), 'UTF-8');
    return $map[$key] ?? 1; // por defecto tratamos como 'Inicial'
}

/**
 * Devuelve cuántas promociones hay disponibles HOY para una categoría dada
 * (fecha vigente, día válido y estado=1).
 */
function getPromocionesDisponiblesPorCategoria(string $categoria): int {
    $pdo = getConnection();

    $sql = "
      SELECT COUNT(*) as total_promociones
        FROM promocion p
        WHERE p.estado = 1
          AND CURDATE() BETWEEN p.desde AND p.hasta
          AND p.categoriaHabilitada = :rankUsuario
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['rankUsuario' => $categoria]);
    return (int)$stmt->fetchColumn();
}

/** Cantidad total de promociones que el cliente YA usó/solicitó (cualquier estado). */
function getPromocionesUsadasPorCliente(int $idUsuario): int {
    $pdo = getConnection();
    $sql = "SELECT COUNT(*) FROM usopromocion WHERE usuarioFk = :u";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['u' => $idUsuario]);
    return (int)$stmt->fetchColumn();
}

/** Cantidad de novedades activas para una categoría (fecha vigente y categoría alcanzable). */
function getNovedadesPorCategoria(string $categoria): int {
    $pdo = getConnection();
    $sql = "
        SELECT COUNT(*) FROM novedad n
        WHERE CURDATE() BETWEEN n.desde AND n.hasta
          AND (
                CASE
                  WHEN LOWER(n.usuarioHabilitado) IN ('inicial','basico','básico') THEN 1
                  WHEN LOWER(n.usuarioHabilitado) IN ('medium','medio') THEN 2
                  WHEN LOWER(n.usuarioHabilitado) IN ('premium','avanzado','rockstar') THEN 3
                  ELSE 1
                END
              ) <= :rankUsuario
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['rankUsuario' => categoria_rank($categoria)]);
    return (int)$stmt->fetchColumn();
}

/**
 * Lista de promociones visibles para el cliente:
 *  - Vigentes por fecha y día
 *  - Estado=1
 *  - Categoría habilitada <= categoría del usuario
 *  - NO solicitadas previamente por este usuario (no exista registro en usopromocion)
 */
function getPromocionesPorCategoria(string $categoria, int $idUsuario): array {
    $pdo = getConnection();
    $sql = "
        SELECT
            p.IDpromocion,
            p.descripcion,
            p.desde,
            p.hasta,
            p.categoriaHabilitada,
            p.dia,
            p.estado,
            p.localFk,
            l.nombre         AS local_nombre,
            l.IDlocal        AS local_codigo,
            l.codigo,
            u.nombre         AS ubicacion_nombre,
            u.Descripcion    AS ubicacion_detalle
        FROM promocion AS p
        JOIN local AS l        ON l.IDlocal = p.localFk
        LEFT JOIN ubicacion AS u ON u.IDubicacion = l.ubicacionFK
        LEFT JOIN usopromocion up
               ON up.promoFK = p.IDpromocion AND up.usuarioFk = :idUsuario
        WHERE p.estado = '1'
          AND CURDATE() BETWEEN p.desde AND p.hasta
          AND (
                (p.dia BETWEEN 0 AND 6 AND p.dia = WEEKDAY(CURDATE())) OR
                (p.dia BETWEEN 1 AND 7 AND p.dia = DAYOFWEEK(CURDATE()))
              )
          AND up.promoFK IS NULL
          AND (
            CASE
              WHEN LOWER(p.categoriaHabilitada) IN ('inicial','basico','básico') THEN 1
              WHEN LOWER(p.categoriaHabilitada) IN ('medium','medio') THEN 2
              WHEN LOWER(p.categoriaHabilitada) IN ('premium','avanzado','rockstar') THEN 3
              ELSE 1
            END
          ) <= :rankUsuario
        ORDER BY p.desde DESC, p.IDpromocion DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'idUsuario'   => $idUsuario,
        'rankUsuario' => categoria_rank($categoria),
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




function getNovedadesRecientesPorCategoria($categoria_usuario, $limite = 8) {
   
    $pdo = getConnection();
    
    // Mapear categorías para comparación (igual que en las promociones)
    $niveles_categoria = [
        'Inicial' => 1,
        'Medium' => 2,
        'Premium' => 3,
    ];
    
    $nivel_usuario = $niveles_categoria[$categoria_usuario] ?? 1;
    
    // Usar bindValue para el límite
    $query = "
        SELECT 
            n.IDnovedad,
            n.cabecera,
            n.descripcion,
            n.desde,
            n.hasta,
            n.usuarioHabilitado as categoriaHabilitada
        FROM novedad n
        WHERE CURDATE() BETWEEN n.desde AND n.hasta
          AND (CASE n.usuarioHabilitado
               WHEN 'Inicial' THEN 1
               WHEN 'Medium' THEN 2
               WHEN 'Premium' THEN 3
               ELSE 1
               END) <= ?
        ORDER BY n.desde DESC
        LIMIT ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $nivel_usuario, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}





function getHistorialUsoCliente($idUsuario, $limite = 8) {
   
    $pdo = getConnection();
    
    // Convertir el límite a entero para evitar inyección SQL
    $limite = (int)$limite;
    
    $query = "
        SELECT 
            up.fechaUso,
            up.estado,
            p.descripcion as descripcion_promo,
            l.nombre as nombre_local,
            l.rubro as rubro_local,
            p.categoriaHabilitada,
            u.nombre as ubicacion_nombre
        FROM usopromocion up
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion
        INNER JOIN local l ON p.localFk = l.IDlocal
        LEFT JOIN ubicacion u ON l.ubicacionFK = u.IDubicacion
        WHERE up.usuarioFk = ?
        ORDER BY up.fechaUso DESC
        LIMIT " . $limite . "
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idUsuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function solicitarPromocion(int $idUsuario, int $idPromocion): array {
    $pdo = getConnection();
    $sqlPromo = "
        SELECT p.IDpromocion
        FROM promocion p
        WHERE p.IDpromocion = :p
          AND p.estado = '1'
          AND CURDATE() BETWEEN p.desde AND p.hasta
        LIMIT 1
    ";
    $st = $pdo->prepare($sqlPromo);
    $st->execute(['p' => $idPromocion]);
    if (!$st->fetchColumn()) {
        return ['ok'=>false,'msg'=>'Promoción no disponible'];
    }

    // Intentamos insertar evitando duplicado por PK compuesta
    try {
        $sqlIns = "
            INSERT INTO usopromocion (usuarioFk, promoFK, fechaUso, estado)
            VALUES (:u, :p, CURDATE(), 'pendiente')
        ";
        $stmt = $pdo->prepare($sqlIns);
        $stmt->execute(['u'=>$idUsuario, 'p'=>$idPromocion]);
        return ['ok'=>true,'msg'=>'Promoción solicitada'];
    } catch (PDOException $e) {
        if ((int)$e->errorInfo[1] === 1062) {
            return ['ok'=>false,'msg'=>'Ya solicitaste esta promoción'];
        }
        return ['ok'=>false,'msg'=>'Error al solicitar: '.$e->getMessage()];
    }
}


if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    header('Content-Type: application/json; charset=utf-8');

    $accion = $_POST['action'] ?? $_POST['accion'] ?? '';
    if ($accion === 'solicitar') {
        $idPromo = (int)($_POST['idPromocion'] ?? 0);
        $idUser  = (int)($_SESSION['IDusuario'] ?? 0);
        if ($idPromo <= 0 || $idUser <= 0) {
            echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit;
        }
        echo json_encode(solicitarPromocion($idUser, $idPromo)); exit;
    }

    echo json_encode(['ok'=>false,'msg'=>'Acción no reconocida']); exit;
}


