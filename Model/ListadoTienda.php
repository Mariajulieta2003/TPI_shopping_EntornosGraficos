<?php


include_once("../Model/conexion.php");

if (!function_exists('listarTiendas')) {
  function listarTiendas(int $limit = 200): array {
    $pdo = getConnection();

    $sql = "
      SELECT
        l.IDlocal,
        l.nombre                                   AS local_nombre,
        l.rubro                                    AS local_rubro,
        l.codigo as codigo,
        l.ubicacionFK,
        u.IDubicacion,
        u.nombre                                   AS ubicacion_nombre,
        COALESCE(u.Descripcion, u.descripcion)     AS ubicacion_descripcion
      FROM `local` AS l
      LEFT JOIN `ubicacion` AS u
        ON u.IDubicacion = l.ubicacionFK
      ORDER BY l.nombre ASC
      LIMIT :lim
    ";

    $st = $pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}