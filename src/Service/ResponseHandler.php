<?php
class ResponseHandler {
    /**
     * 发送成功响应
     *
     * @param mixed $data 响应数据
     * @param string $message 响应消息
     * @param int $code HTTP状态码
     */
    public static function success($data = null, $message = '操作成功', $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * 发送错误响应
     *
     * @param string $message 错误消息
     * @param int $code HTTP状态码
     * @param int $httpCode HTTP状态码
     */
    public static function error($message = '操作失败', $code = 400, $httpCode = 400) {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code
        ]);
        exit;
    }
    
    /**
     * 发送文件下载响应
     *
     * @param string $filePath 文件路径
     * @param string $fileName 文件名
     * @param string $mimeType MIME类型
     */
    public static function file($filePath, $fileName, $mimeType) {
        if (!file_exists($filePath)) {
            self::error('文件不存在', 404, 404);
        }
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . rawurlencode($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}