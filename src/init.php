<?php
// 统一的初始化文件，用于加载配置和依赖

// 设置错误报告级别
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// 加载配置文件
$config = require __DIR__ . '/config.php';

// 设置错误日志路径
ini_set('error_log', $config['log_file']);

// 自动加载类文件
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/Service/',
        __DIR__ . '/Controller/',
        __DIR__ . '/Model/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 初始化日志记录器（在自动加载之后）
Logger::init($config['log_file']);

// 返回配置
return $config;