<?php
// Model/NovedadesModel.php
// Usa getConnection() y expone funciones simples para novedades.
include_once("../Model/conexion.php");

if (!function_exists('listarNovedadesVigentes')) {
  /**
   * Lista novedades vigentes (hoy entre desde y hasta), ordenadas por fecha (desc).
   * @param int $limit
   * @return array<int, array<string,mixed>>
   */
  function listarNovedadesVigentes(int $limit = 30): array {
    $pdo = getConnection();

    $sql = "
      SELECT
        n.IDnovedad,
        n.desde,
        n.hasta,
        n.usuarioHabilitado,
        n.descripcion,
        n.cabecera,
        n.cuerpo
      FROM novedad n
      WHERE CURDATE() BETWEEN n.desde AND n.hasta
      ORDER BY n.desde DESC, n.IDnovedad DESC
      LIMIT :lim
    ";

    $st = $pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}
