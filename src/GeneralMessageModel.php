<?php
// src/GeneralMessageModel.php – CRUD for homepage general messages

class GeneralMessageModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Return all visible messages, newest first. */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT id, body, type, created_at
             FROM general_messages
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, body, type, created_at
             FROM general_messages
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $body, string $type): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO general_messages (body, type)
             VALUES (:body, :type)
             RETURNING id'
        );
        $stmt->execute([':body' => $body, ':type' => $type]);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, string $body, string $type): void
    {
        $stmt = $this->db->prepare(
            'UPDATE general_messages SET body = :body, type = :type
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':body' => $body, ':type' => $type, ':id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE general_messages SET deleted_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    /** Allowed message types. */
    public static function types(): array
    {
        return ['info', 'warning', 'danger', 'success'];
    }
}
