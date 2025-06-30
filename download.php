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

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('缺少文件ID');
}

$fileId = filter_var($_GET['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
try {
    $fileInfo = $fileManager->downloadFile($fileId);
    if (!$fileInfo) {
        throw new Exception("文件不存在");
    }
    header('Content-Type: ' . $fileInfo['mimeType']);
    header('Content-Disposition: attachment; filename="' . rawurlencode($fileInfo['originalName']) . '"');
    header('Content-Length: ' . $fileInfo['size']);
    readfile($fileInfo['path']);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    exit('文件不存在');
}