<?php

namespace App;

class Logger {
    private string $logFile;

    public function __construct(Config $config) {
        $this->logFile = $config->get('logging.log_file');

        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
    }

    public function log(string $message, string $level = 'INFO'): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "{$timestamp} [{$level}] - {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    public function info(string $message): void {
        $this->log($message, 'INFO');
    }

    public function warning(string $message): void {
        $this->log($message, 'WARNING');
    }

    public function error(string $message): void {
        $this->log($message, 'ERROR');
    }
}
