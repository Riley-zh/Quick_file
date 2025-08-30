<?php
class FileManager {
    private $storageDir;
    private $encryption;
    private $config;

    public function __construct($storageDir, $encryption) {
        $this->storageDir = $storageDir;
        $this->encryption = $encryption;
        $this->config = require __DIR__ . '/../config.php';

        if (!is_dir($this->storageDir)) {
            if (!mkdir($this->storageDir, 0755, true)) {
                // 如果目录无法创建，记录日志但不抛出异常
                error_log("无法创建存储目录: " . $this->storageDir);
            }
        }
    }

    public function uploadFile($file, $fileName) {
        $this->validateFile($file);

        $fileId = bin2hex(random_bytes(16));
        $fileDir = $this->storageDir . '/' . $fileId;
        mkdir($fileDir, 0755);

        move_uploaded_file($file['tmp_name'], $fileDir . '/file.bin');

        $fileSize = filesize($fileDir . '/file.bin');
        $mimeType = mime_content_type($fileDir . '/file.bin');
        $metadata = [
            'originalName' => $fileName,
            'size' => $fileSize,
            'mimeType' => $mimeType,
            'uploadedAt' => time()
        ];

        $encryptedMetadata = $this->encryption->encryptString(json_encode($metadata));
        file_put_contents($fileDir . '/meta.json', $encryptedMetadata);

        return $fileId;
    }

    private function validateFile($file) {
        // 使用已加载的配置，避免重复加载
        if ($file['size'] > $this->config['max_file_size']) {
            throw new Exception("文件大小超过限制（最大" . floor($this->config['max_file_size'] / 1024 / 1024) . "MB）");
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExtension), $this->config['allowed_extensions'])) {
            throw new Exception("不支持的文件类型");
        }

        $this->validateFileType($file['tmp_name'], $fileExtension);
    }

    private function validateFileType($filePath, $fileExtension) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed'
        ];

        $fileExtension = strtolower($fileExtension);
        if (!isset($allowedMimeTypes[$fileExtension]) || $allowedMimeTypes[$fileExtension] !== $mimeType) {
            throw new Exception("文件类型不匹配或不支持");
        }
    }

    public function getFileList($offset = 0, $limit = 10) {
        $files = [];
        $fileItems = [];

        if (!is_dir($this->storageDir)) {
            return $files;
        }

        $dir = scandir($this->storageDir);
        foreach ($dir as $fileId) {
            if ($fileId === '.' || $fileId === '..') continue;

            $filePath = $this->storageDir . '/' . $fileId;

            if (is_dir($filePath) && file_exists($filePath . '/meta.json')) {
                try {
                    $metaData = $this->encryption->decryptString(file_get_contents($filePath . '/meta.json'));
                    $meta = json_decode($metaData, true);
                    $fileItems[] = [
                        'id' => $fileId,
                        'name' => $meta['originalName'],
                        'size' => $meta['size'],
                        'mimeType' => $meta['mimeType'],
                        'uploadedAt' => $meta['uploadedAt'],
                        'meta' => $meta
                    ];
                } catch (Exception $e) {
                    error_log("元数据读取错误: " . $e->getMessage() . " | 文件ID: " . $fileId);
                    continue;
                }
            }
        }

        usort($fileItems, function($a, $b) {
            return $b['uploadedAt'] - $a['uploadedAt'];
        });

        $pagedFiles = array_slice($fileItems, $offset, $limit);

        foreach ($pagedFiles as &$file) {
            $file['uploadedAt'] = date('Y-m-d H:i:s', $file['uploadedAt']);
        }

        return $pagedFiles;
    }

    public function getTotalFiles() {
        if (!is_dir($this->storageDir)) {
            return 0;
        }

        $total = 0;
        $dir = scandir($this->storageDir);

        foreach ($dir as $fileId) {
            if ($fileId === '.' || $fileId === '..') continue;

            $filePath = $this->storageDir . '/' . $fileId;

            if (is_dir($filePath) && file_exists($filePath . '/meta.json')) {
                $total++;
            }
        }

        return $total;
    }

    public function deleteFile($fileId) {
        $filePath = $this->storageDir . '/' . $fileId;

        if (is_dir($filePath)) {
            if (file_exists($filePath . '/meta.json')) {
                $files = glob($filePath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }

                rmdir($filePath);
                return true;
            } else {
                throw new Exception("元数据文件不存在，无法安全删除: $filePath");
            }
        } else {
            throw new Exception("文件目录不存在: $filePath");
        }
    }

    public function downloadFile($fileId) {
        $filePath = $this->storageDir . '/' . $fileId;

        if (is_dir($filePath) && file_exists($filePath . '/meta.json')) {
            try {
                $metaData = $this->encryption->decryptString(file_get_contents($filePath . '/meta.json'));
                $meta = json_decode($metaData, true);
                $meta['path'] = $filePath . '/file.bin';
                return $meta;
            } catch (Exception $e) {
                error_log("元数据读取错误: " . $e->getMessage() . " | 文件ID: " . $fileId);
                return false;
            }
        }

        return false;
    }

    public function searchFiles($keyword, $offset = 0, $limit = 10) {
        $files = [];
        $fileItems = [];

        if (!is_dir($this->storageDir)) {
            return $files;
        }

        $dir = scandir($this->storageDir);
        foreach ($dir as $fileId) {
            if ($fileId === '.' || $fileId === '..') continue;

            $filePath = $this->storageDir . '/' . $fileId;

            if (is_dir($filePath) && file_exists($filePath . '/meta.json')) {
                try {
                    $metaData = $this->encryption->decryptString(file_get_contents($filePath . '/meta.json'));
                    $meta = json_decode($metaData, true);
                    
                    if (stripos($meta['originalName'], $keyword) !== false) {
                        $fileItems[] = [
                            'id' => $fileId,
                            'name' => $meta['originalName'],
                            'size' => $meta['size'],
                            'mimeType' => $meta['mimeType'],
                            'uploadedAt' => $meta['uploadedAt'],
                            'meta' => $meta
                        ];
                    }
                } catch (Exception $e) {
                    error_log("元数据读取错误: " . $e->getMessage() . " | 文件ID: " . $fileId);
                    continue;
                }
            }
        }

        usort($fileItems, function($a, $b) {
            return $b['uploadedAt'] - $a['uploadedAt'];
        });

        $pagedFiles = array_slice($fileItems, $offset, $limit);

        foreach ($pagedFiles as &$file) {
            $file['uploadedAt'] = date('Y-m-d H:i:s', $file['uploadedAt']);
        }

        return $pagedFiles;
    }
}







