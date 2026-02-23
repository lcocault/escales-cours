<?php
// src/SessionModel.php – cooking-session CRUD

class SessionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Upcoming sessions visible to the public
    public function getUpcoming(): array
    {
        $stmt = $this->db->query(
            "SELECT id, title, theme, session_date, start_time, end_time,
                    max_attendees, remaining_seats, price_cents, summary
             FROM sessions
             WHERE session_date >= CURRENT_DATE AND deleted_at IS NULL
             ORDER BY session_date ASC, start_time ASC"
        );
        return $stmt->fetchAll();
    }

    // All sessions for admin
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM sessions WHERE deleted_at IS NULL ORDER BY session_date DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM sessions WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO sessions
                (title, theme, session_date, start_time, end_time,
                 max_attendees, remaining_seats, price_cents,
                 summary, objectives, theoretical_content, recipe)
             VALUES
                (:title, :theme, :session_date, :start_time, :end_time,
                 :max_attendees, :max_attendees, :price_cents,
                 :summary, :objectives, :theoretical_content, :recipe)
             RETURNING id'
        );
        $stmt->execute([
            ':title'               => $data['title'],
            ':theme'               => $data['theme'],
            ':session_date'        => $data['session_date'],
            ':start_time'          => $data['start_time'],
            ':end_time'            => $data['end_time'],
            ':max_attendees'       => (int) $data['max_attendees'],
            ':price_cents'         => (int) $data['price_cents'],
            ':summary'             => $data['summary'] ?? null,
            ':objectives'          => $data['objectives'] ?? null,
            ':theoretical_content' => $data['theoretical_content'] ?? null,
            ':recipe'              => $data['recipe'] ?? null,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE sessions SET
                title = :title, theme = :theme, session_date = :session_date,
                start_time = :start_time, end_time = :end_time,
                max_attendees = :max_attendees, price_cents = :price_cents,
                summary = :summary, objectives = :objectives,
                theoretical_content = :theoretical_content, recipe = :recipe
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            ':title'               => $data['title'],
            ':theme'               => $data['theme'],
            ':session_date'        => $data['session_date'],
            ':start_time'          => $data['start_time'],
            ':end_time'            => $data['end_time'],
            ':max_attendees'       => (int) $data['max_attendees'],
            ':price_cents'         => (int) $data['price_cents'],
            ':summary'             => $data['summary'] ?? null,
            ':objectives'          => $data['objectives'] ?? null,
            ':theoretical_content' => $data['theoretical_content'] ?? null,
            ':recipe'              => $data['recipe'] ?? null,
            ':id'                  => $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE sessions SET deleted_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    public function confirmSession(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE sessions SET status = 'confirmed' WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
    }

    public function cancelSession(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE sessions SET status = 'cancelled' WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
    }

    /**
     * Returns pending sessions whose start datetime falls within the next 24 hours.
     * Used by the cron job to decide whether to confirm or cancel each session.
     */
    public function getSessionsDueForCheck(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, session_date, start_time, end_time, status
             FROM sessions
             WHERE deleted_at IS NULL
               AND status = 'pending'
               AND (session_date + start_time)::timestamp
                   BETWEEN NOW()::timestamp
                       AND (NOW() + INTERVAL '24 hours')::timestamp
             ORDER BY session_date ASC, start_time ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function decrementSeats(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE sessions SET remaining_seats = remaining_seats - 1
             WHERE id = :id AND remaining_seats > 0'
        );
        $stmt->execute([':id' => $id]);
    }

    public function incrementSeats(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE sessions SET remaining_seats = remaining_seats + 1
             WHERE id = :id AND remaining_seats < max_attendees'
        );
        $stmt->execute([':id' => $id]);
    }
}
