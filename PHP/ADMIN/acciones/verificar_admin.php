<?php
function verificarAccesoAdmin() {
    if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
        if (headers_sent()) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        } else {
            header('Location: ../../inicio_sesion.php?error=acceso_denegado');
            exit;
        }
    }
}
?>