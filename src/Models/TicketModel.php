<?php
namespace TicketTracker\Models;

class TicketModel extends BasicModel
{
    public function __construct()
    {
        parent::__construct('tickets');
    }

    public function getByUserId(int $userId): array
    {
        //here we should implement messages join
        $stmt = $this->connection->prepare("
            SELECT t.*, s.name as status_name, tag.name as tag_name,
                   author.username as author_name
            FROM {$this->tableName} t
            LEFT JOIN statuses s ON t.status_id = s.id
            LEFT JOIN tags tag ON t.tag_id = tag.id
            LEFT JOIN users author ON t.author_id = author.id
            WHERE t.author_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    public function getAll(): array
    {
        $sql = "
            SELECT t.*, s.name as status_name, tag.name as tag_name,
                   author.username as author_name
            FROM {$this->tableName} t
            LEFT JOIN statuses s ON t.status_id = s.id
            LEFT JOIN tags tag ON t.tag_id = tag.id
            LEFT JOIN users author ON t.author_id = author.id
            ORDER BY t.created_at DESC
        ";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_OBJ) ?: [];
    }

    public function create(array $data): int
    {
        $ticketData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'tag_id' => $data['tag_id'],
            'author_id' => $data['author_id'],
            'updater_id' => $data['author_id'],
        ];

        return parent::create($ticketData);
    }

    public function updateStatus(int $ticketId, string $statusId, int $updaterId): bool
    {
        $data = [
            'updater_id' => $updaterId,
            'status_id' => $statusId,
        ];
        return $this->update($ticketId, $data);
    }

    public function updateTag(int $ticketId, string $tagId, int $updaterId): bool
    {
        $data = [
            'updater_id' => $updaterId,
            'tag_id' => $tagId,
        ];
        return $this->update($ticketId, $data);
    }

    public function updateMessage(int $ticketId, string $messageId, int $updaterId): bool
    {
        $data = [
            'updater_id' => $updaterId,
            'message_id' => $messageId,
        ];
        return $this->update($ticketId, $data);
    }
}