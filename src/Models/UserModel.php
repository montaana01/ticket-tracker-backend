<?php
namespace TicketTracker\Models;

class UserModel extends BasicModel
{
    public function __construct()
    {
        parent::__construct('users');
    }

    /** @return array<string, mixed>|null */
    public function getByUsername(string $username): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}