<?php
session_start();
if (isset($_SESSION['user_name'])) {
    echo json_encode(['user_name' => $_SESSION['user_name'], 'user_rol' => $_SESSION['user_rol']]);
} else {
    echo json_encode(['user_name' => null, 'user_rol' => null]);
}
?>