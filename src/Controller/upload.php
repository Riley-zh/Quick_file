<?php
// 加载初始化文件
require_once __DIR__ . '/../init.php';
$config = require __DIR__ . '/../config.php';

// 创建服务实例
$fileManager = new FileManager($config['storage_dir'], new Encryption($config['encryption_key']));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseHandler::error('方法不允许', 405, 405);
}

try {
    if (empty($_FILES['file']['tmp_name'])) {
        ResponseHandler::error("上传的文件为空");
    }

    $file = $_FILES['file'];
    $fileName = filter_var($_POST['fileName'] ?? $file['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $fileId = $fileManager->uploadFile($file, $fileName);
    $files = $fileManager->getFileList();

    ResponseHandler::success([
        'fileId' => $fileId,
        'files' => $files
    ], '文件上传成功');
} catch (Exception $e) {
    ResponseHandler::error($e->getMessage());
}