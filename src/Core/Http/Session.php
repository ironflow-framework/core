<?php

namespace IronFlow\Core\Http;

use IronFlow\Core\Database\Database;
use PDO;

class Session
{
    protected array $data = [];
    protected string $id;
    protected static ?PDO $pdo = null;
    protected static string $table = 'sessions';
    protected bool $started = false;

    public function __construct()
    {
        if (!self::$pdo) {
            self::$pdo = Database::getInstance()->getConnection();
        }
        $this->id = $_COOKIE['IRONFLOWSESSID'] ?? bin2hex(random_bytes(16));
    }

    public function start(): void
    {
        if ($this->started) return;
        $this->load();
        setcookie('IRONFLOWSESSID', $this->id, time() + 3600, '/');
        $this->started = true;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
        $this->save();
    }

    public function all(): array
    {
        return $this->data;
    }

    public function destroy(): void
    {
        $this->data = [];
        $stmt = self::$pdo->prepare('DELETE FROM ' . self::$table . ' WHERE id = ?');
        $stmt->execute([$this->id]);
        setcookie('IRONFLOWSESSID', '', time() - 3600, '/');
    }

    protected function load(): void
    {
        $stmt = self::$pdo->prepare('SELECT data FROM ' . self::$table . ' WHERE id = ?');
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->data = $row ? unserialize($row['data']) : [];
    }

    protected function save(): void
    {
        $stmt = self::$pdo->prepare('REPLACE INTO ' . self::$table . ' (id, data, updated_at) VALUES (?, ?, NOW())');
        $stmt->execute([$this->id, serialize($this->data)]);
    }
}
