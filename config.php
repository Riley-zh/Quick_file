<?php
return [
    'encryption_key' => 'qW8LG3XxQO7IXmNQ4TMAtNjp3mPc7pjZ',
    'storage_dir' => __DIR__ . '/storage/data',
    'chunks_dir' => __DIR__ . '/storage/chunks',
    'max_file_size' => 100 * 1024 * 1024,
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', '7z'],
    'log_file' => __DIR__ . '/error.log',
    'session_lifetime' => 3600
];