<?php
class Logger {
    private static $logFile;
    
    public static function init($logFile) {
        self::$logFile = $logFile;
    }
    
    public static function info($message) {
        self::log('INFO', $message);
    }
    
    public static function error($message) {
        self::log('ERROR', $message);
    }
    
    public static function warning($message) {
        self::log('WARNING', $message);
    }
    
    private static function log($level, $message) {
        if (!self::$logFile) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;
        
        // 使用UTF-8编码写入文件
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
?>