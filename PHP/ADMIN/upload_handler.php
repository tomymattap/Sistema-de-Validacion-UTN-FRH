<?php
session_start();

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header("Location: ../iniciosesion.php");
    exit();
}

$upload_dir = __DIR__ . '/cert_uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$response = ['status' => 'error', 'message' => 'No se subió ningún archivo.'];

if (!empty($_FILES)) {
    $file_key = key($_FILES);
    $file = $_FILES[$file_key];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_type = mime_content_type($file['tmp_name']);
        if ($file_type == 'image/png') {
            $file_name = uniqid() . '_' . basename($file['name']);
            $upload_path = $upload_dir . $file_name;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $response = ['status' => 'success', 'filename' => $file_name];
            } else {
                $response['message'] = 'Error al mover el archivo subido.';
            }
        } else {
            $response['message'] = 'Tipo de archivo no permitido. Solo se aceptan PNG.';
        }
    } else {
        $response['message'] = 'Error en la subida del archivo: ' . $file['error'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>