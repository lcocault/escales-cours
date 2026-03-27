<?php
// src/RatingModel.php – session rating CRUD

class RatingModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(
        int $bookingId,
        int $userId,
        int $sessionId,
        int $stars,
        string $comment,
        bool $isAnonymous
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO ratings (booking_id, user_id, session_id, stars, comment, is_anonymous)
             VALUES (:bid, :uid, :sid, :stars, :comment, :anon)
             RETURNING id'
        );
        $stmt->execute([
            ':bid'     => $bookingId,
            ':uid'     => $userId,
            ':sid'     => $sessionId,
            ':stars'   => $stars,
            ':comment' => $comment !== '' ? $comment : null,
            ':anon'    => $isAnonymous ? 'TRUE' : 'FALSE',
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function findByUserAndSession(int $userId, int $sessionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ratings WHERE user_id = :uid AND session_id = :sid'
        );
        $stmt->execute([':uid' => $userId, ':sid' => $sessionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getBySession(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.first_name, u.last_name
             FROM ratings r
             JOIN users u ON u.id = r.user_id
             WHERE r.session_id = :sid
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([':sid' => $sessionId]);
        return $stmt->fetchAll();
    }

    public function getAverageBySession(int $sessionId): ?float
    {
        $stmt = $this->db->prepare(
            'SELECT AVG(stars) FROM ratings WHERE session_id = :sid'
        );
        $stmt->execute([':sid' => $sessionId]);
        $avg = $stmt->fetchColumn();
        return ($avg !== false && $avg !== null) ? (float) $avg : null;
    }

    public function countBySession(int $sessionId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ratings WHERE session_id = :sid'
        );
        $stmt->execute([':sid' => $sessionId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Return all ratings across all sessions, most recent first.
     * Includes user name and session title/date.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT r.id, r.session_id, r.user_id, r.stars, r.comment,
                    r.is_anonymous, r.created_at,
                    u.first_name, u.last_name,
                    s.title AS session_title, s.session_date
             FROM ratings r
             JOIN users    u ON u.id = r.user_id
             JOIN sessions s ON s.id = r.session_id
             ORDER BY r.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function getOverallAverage(): ?float
    {
        $avg = $this->db->query('SELECT AVG(stars) FROM ratings')->fetchColumn();
        return ($avg !== false && $avg !== null) ? (float) $avg : null;
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ratings')->fetchColumn();
    }
}
