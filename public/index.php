<?php
// 简化API访问的路由文件
$path = $_GET['path'] ?? '';

// 根据路径分发请求到相应的控制器
switch ($path) {
    case 'upload':
        require __DIR__ . '/../src/Controller/upload.php';
        break;
    case 'list':
        require __DIR__ . '/../src/Controller/list.php';
        break;
    case 'delete':
        require __DIR__ . '/../src/Controller/delete.php';
        break;
    case 'download':
        require __DIR__ . '/../src/Controller/download.php';
        break;
    default:
        // 默认返回前端页面
        if (empty($path)) {
            readfile(__DIR__ . '/index.html');
        } else {
            http_response_code(404);
            echo 'API endpoint not found';
        }
        break;
}