<?php

namespace IronFlow\Core\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerService
{
    protected Logger $logger;

    public function __construct(string $name = 'app', string $logFile = 'storage/logs/app.log')
    {
        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}
