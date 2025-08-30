<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Service/encryption.php';
require_once __DIR__ . '/../Service/file_manager.php';

$config = require 'config.php';
$fileManager = new FileManager($config['storage_dir'], new Encryption($config['encryption_key']));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '方法不允许']);
    exit;
}

try {
    if (empty($_FILES['file']['tmp_name'])) {
        throw new Exception("上传的文件为空");
    }

    $file = $_FILES['file'];
    $fileName = filter_var($_POST['fileName'] ?? $file['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $fileId = $fileManager->uploadFile($file, $fileName);
    $files = $fileManager->getFileList();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '文件上传成功',
        'fileId' => $fileId,
        'files' => $files
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}