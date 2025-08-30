<?php
// 加载初始化文件
$config = require_once __DIR__ . '/../init.php';

// 创建服务实例
$fileManager = new FileManager($config['storage_dir'], new Encryption($config['encryption_key']));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseHandler::error('方法不允许', 405, 405);
}

try {
    $fileId = $_POST['id'] ?? null;
    if (empty($fileId)) {
        ResponseHandler::error("文件ID为空");
    }

    if (!$fileManager->deleteFile($fileId)) {
        ResponseHandler::error("文件删除失败");
    }

    $files = $fileManager->getFileList();

    ResponseHandler::success([
        'files' => $files
    ], '文件已成功删除');
} catch (Exception $e) {
    ResponseHandler::error($e->getMessage());
}