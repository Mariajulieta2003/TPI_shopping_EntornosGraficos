<?php
// Model/promocionesModel.php
// Usa getConnection() de tu conexion.php y expone funciones simples.

include_once("../Model/conexion.php");

/**
 * Quienes pueden usar la promo según su categoría mínima.
 * - Inicial  => Inicial, Medium, Premium
 * - Medium   => Medium,  Premium
 * - Premium  => Premium
 * - null/otro=> Todos
 */
function categoriasPermitidas(?string $min): array {
    $min = $min ? strtolower(trim($min)) : '';
    switch ($min) {
        case 'inicial': return ['Inicial','Medium','Premium'];
        case 'medium':  return ['Medium','Premium'];
        case 'premium': return ['Premium'];
        default:        return ['Todos'];
    }
}

/**
 * Lista promociones vigentes (hoy entre desde y hasta), unidas al local,
 * ordenadas por nivel de categoriaHabilitada: Inicial < Medium < Premium.
 * Sin filtros extra, sin fotos.
 */
function listarPromocionesVigentes(int $limit = 50): array {
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
            l.IDlocal,
            l.nombre AS local_nombre,
            l.rubro  AS local_rubro
        FROM promocion p
        INNER JOIN `local` l ON l.IDlocal = p.localFk
        WHERE CURDATE() BETWEEN p.desde AND p.hasta
        -- Si manejás habilitación por estado, descomentá y ajustá:
        -- AND p.estado = 'activa'
        ORDER BY
          CASE LOWER(COALESCE(p.categoriaHabilitada,'')) 
            WHEN 'inicial' THEN 1
            WHEN 'medium'  THEN 2
            WHEN 'premium' THEN 3
            ELSE 4
          END ASC,
          p.IDpromocion DESC
        LIMIT :lim
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Campo calculado para la vista (badges en el modal)
    foreach ($rows as &$r) {
        $r['categorias_permitidas'] = categoriasPermitidas($r['categoriaHabilitada'] ?? null);
    }
    unset($r);

    return $rows;
}
