<?php

session_start();
require_once '../Model/ProcesarDashboardTienda.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['IDusuario'];
    $local = getLocalPorUsuario($idUsuario);
    
    if (!$local) {
        echo json_encode(['success' => false, 'message' => 'No se encontró el local.']);
        exit;
    }
    
    $idLocal = $local['IDlocal'];

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'eliminar_promocion':
                $idPromocion = $_POST['idPromocion'];
                if (eliminarPromocion($idPromocion, $idLocal)) {
                    echo json_encode(['success' => true, 'message' => 'Promoción desactivada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al desactivar la promoción.']);
                }
                exit;

            case 'aceptar_solicitud':
                $usuarioFk = $_POST['usuarioFk'];
                $promoFK = $_POST['promoFK'];
                if (actualizarEstadoSolicitud($usuarioFk, $promoFK, 1)) {
                    echo json_encode(['success' => true, 'message' => 'Solicitud aceptada.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al aceptar la solicitud.']);
                }
                exit;

            case 'rechazar_solicitud':
                $usuarioFk = $_POST['usuarioFk'];
                $promoFK = $_POST['promoFK'];
                if (actualizarEstadoSolicitud($usuarioFk, $promoFK, 2)) {
                    echo json_encode(['success' => true, 'message' => 'Solicitud rechazada.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al rechazar la solicitud.']);
                }
                exit;
        }
    }
}
?>