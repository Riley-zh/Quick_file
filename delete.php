<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

require_once 'config.php';
require_once 'encryption.php';
require_once 'file_manager.php';

$config = require 'config.php';
$fileManager = new FileManager($config['storage_dir'], new Encryption($config['encryption_key']));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '方法不允许']);
    exit;
}

try {
    $fileId = $_POST['id'] ?? null;
    if (empty($fileId)) {
        throw new Exception("文件ID为空");
    }

    if (!$fileManager->deleteFile($fileId)) {
        throw new Exception("文件删除失败");
    }

    $files = $fileManager->getFileList();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '文件已成功删除',
        'files' => $files
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}