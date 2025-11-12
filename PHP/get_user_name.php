<?php
session_start();
header('Content-Type: application/json');

// Devuelve el nombre, rol y el ID del usuario logueado.
if (isset($_SESSION['user_name'], $_SESSION['user_rol'], $_SESSION['user_id'])) {
    echo json_encode(['user_name' => $_SESSION['user_name'], 'user_rol' => $_SESSION['user_rol'], 'user_id' => $_SESSION['user_id']]);
} else {
    echo json_encode(['user_name' => null, 'user_rol' => null, 'user_id' => null]);
}
?>