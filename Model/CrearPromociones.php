<?php
include_once("../Model/conexion.php");


function getLocalPorUsuario($idUsuario) {
    $pdo = getConnection();
    $query = "SELECT IDlocal, nombre, rubro FROM local WHERE usuarioFK = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idUsuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function crearPromocion($datos) {
    $pdo = getConnection();
    $query = "
        INSERT INTO promocion 
        (descripcion, desde, hasta, categoriaHabilitada, dia, estado, localFk) 
        VALUES (?, ?, ?, ?, ?, 1, ?)
    ";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([
        $datos['descripcion'],
        $datos['desde'],
        $datos['hasta'],
        $datos['categoria'],
        $datos['dia'],
        $datos['local_id']
    ]);
}


?>