<?php
class Encryption {
    private $key;

    public function __construct($key) {
        $this->key = $key;
        $this->checkEnvironment();
    }

    private function checkEnvironment() {
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL 扩展未启用，无法进行加密。请联系管理员启用 OpenSSL。');
        }

        if (!function_exists('openssl_encrypt')) {
            throw new Exception('OpenSSL 函数不可用，请检查 PHP 配置。');
        }
    }

    public function encryptString($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . '::' . $encrypted);
    }

    public function decryptString($encryptedData) {
        $data = base64_decode($encryptedData);
        list($iv, $encrypted) = explode('::', $data, 2);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
    }

    public function encryptFile($sourceFile, $destinationDir, $fileName) {
        $config = require __DIR__ . '/../config.php';
        $chunkSize = $config['max_chunk_size'];
        $fileSize = filesize($sourceFile);
        $chunksCount = ceil($fileSize / $chunkSize);

        $mimeType = mime_content_type($sourceFile);

        $fileMeta = [
            'originalName' => $fileName,
            'size' => $fileSize,
            'chunks' => $chunksCount,
            'mimeType' => $mimeType,
            'uploadedAt' => time()
        ];

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $fileId = bin2hex(random_bytes(16));
        $fileDir = $destinationDir . '/' . $fileId;

        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755, true);
        }

        $file = fopen($sourceFile, 'rb');
        for ($i = 0; $i < $chunksCount; $i++) {
            $chunkData = fread($file, $chunkSize);
            $encryptedChunk = openssl_encrypt($chunkData, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
            file_put_contents($fileDir . "/chunk_$i.bin", $encryptedChunk);
        }
        fclose($file);

        $metaData = json_encode(array_merge($fileMeta, ['iv' => base64_encode($iv)]));
        file_put_contents($fileDir . '/meta.json', $this->encryptString($metaData));

        return $fileId;
    }

    public function decryptFile($fileId, $destinationFile) {
        $config = require __DIR__ . '/../config.php';
        $fileDir = $config['storage_dir'] . '/' . $fileId;

        if (!is_dir($fileDir)) {
            throw new Exception("文件不存在");
        }

        $metaData = $this->decryptString(file_get_contents($fileDir . '/meta.json'));
        $meta = json_decode($metaData, true);

        $iv = base64_decode($meta['iv']);
        $chunksCount = $meta['chunks'];

        $outputFile = fopen($destinationFile, 'wb');
        for ($i = 0; $i < $chunksCount; $i++) {
            $encryptedChunk = file_get_contents($fileDir . "/chunk_$i.bin");
            $chunkData = openssl_decrypt($encryptedChunk, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
            fwrite($outputFile, $chunkData);
        }
        fclose($outputFile);
    }
}