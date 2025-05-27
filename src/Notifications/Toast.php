<?php

declare(strict_types=1);

namespace IronFlow\Notifications;

class Toast
{
    protected string $message;
    protected string $type;
    protected ?string $channel;
    protected array $options;

    public function __construct(string $message, string $type = 'info', ?string $channel = 'notifications', array $options = [])
    {
        $this->message = $message;
        $this->type = $type;
        $this->channel = $channel;
        $this->options = array_merge([
            'duration' => 3000,
            'position' => 'top-right',
            'closeButton' => true,
            'progressBar' => true,
        ], $options);
    }

    public function send(): void
    {
        $data = [
            'type' => $this->type,
            'message' => $this->message,
            'options' => $this->options,
        ];

        if ($this->channel) {
            \app('channel')->broadcastToAll($this->channel, $data);
        }
    }

    public static function info(string $message, ?string $channel = 'notifications', array $options = []): self
    {
        return new static($message, 'info', $channel, $options);
    }

    public static function success(string $message, ?string $channel = 'notifications', array $options = []): self
    {
        return new static($message, 'success', $channel, $options);
    }

    public static function warning(string $message, ?string $channel = 'notifications', array $options = []): self
    {
        return new static($message, 'warning', $channel, $options);
    }

    public static function error(string $message, ?string $channel = 'notifications', array $options = []): self
    {
        return new static($message, 'error', $channel, $options);
    }
}
