<?php
// 加载初始化文件
$config = require_once __DIR__ . '/../init.php';

// 创建服务实例
$fileManager = new FileManager($config['storage_dir'], new Encryption($config['encryption_key']));

if (!isset($_GET['id'])) {
    ResponseHandler::error('缺少文件ID', 400, 400);
}

$fileId = filter_var($_GET['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
try {
    $fileInfo = $fileManager->downloadFile($fileId);
    if (!$fileInfo) {
        ResponseHandler::error("文件不存在", 404, 404);
    }
    
    ResponseHandler::file($fileInfo['path'], $fileInfo['originalName'], $fileInfo['mimeType']);
} catch (Exception $e) {
    ResponseHandler::error($e->getMessage(), 404, 404);
}