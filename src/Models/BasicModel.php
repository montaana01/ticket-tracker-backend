<?php

namespace TicketTracker\Models;

use TicketTracker\Config\DB;

class BasicModel
{
    protected string $tableName = '';
    protected \PDO $connection;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        if ($_ENV) {
            $config = [
                'db' => [
                    'host' => $_ENV['DB_HOST'],
                    'port' => $_ENV['DB_PORT'],
                    'dbname' => $_ENV['DB_NAME'],
                    'username' => $_ENV['DB_USER'],
                    'password' => $_ENV['DB_PASS'],
                    'options' => $_ENV['DB_OPTIONS']
                ],
            ];
        } else {
            $config = require __DIR__ . '/../Config/params.php';
        }
        $this->connection = (new DB($config['db']))->db;

    }

    public function get(int $id): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->tableName} WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        $stmt = $this->connection->query($sql);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->tableName} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_values($data));

        return (int)$this->connection->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $setPlaceHolders = [];
        $params = [];

        foreach ($data as $field => $value) {
            $setPlaceHolders[] = "{$field} = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setPlaceHolders) . " WHERE id = ?";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM {$this->tableName} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}