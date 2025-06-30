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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '方法不允许']);
    exit;
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

try {
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $files = $fileManager->searchFiles($_GET['search'], $offset, $limit);
        $totalFiles = count($files);
    } else {
        $files = $fileManager->getFileList($offset, $limit);
        $totalFiles = $fileManager->getTotalFiles();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'files' => $files,
        'totalFiles' => $totalFiles
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '获取文件列表失败: ' . $e->getMessage()]);
}