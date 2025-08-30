<?php
// 加载初始化文件
$config = require_once __DIR__ . '/../init.php';

// 创建服务实例
$fileManager = new FileManager($config['storage_dir'], new Encryption($config['encryption_key']));

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ResponseHandler::error('方法不允许', 405, 405);
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
    
    ResponseHandler::success([
        'files' => $files,
        'totalFiles' => $totalFiles
    ]);
} catch (Exception $e) {
    ResponseHandler::error('获取文件列表失败: ' . $e->getMessage(), 500, 500);
}