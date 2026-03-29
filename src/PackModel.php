<?php
// src/PackModel.php – pack CRUD and availability helpers

class PackModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Retrieval ─────────────────────────────────────────────────────────────

    /**
     * Returns all non-deleted packs, each enriched with a session count.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*,
                    COUNT(ps.session_id) AS session_count
             FROM packs p
             LEFT JOIN pack_sessions ps ON ps.pack_id = p.id
             WHERE p.deleted_at IS NULL
             GROUP BY p.id
             ORDER BY p.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Returns a single pack or null if not found / deleted.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM packs WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Returns all sessions belonging to a pack (full session row).
     */
    public function getSessionsForPack(int $packId): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*
             FROM sessions s
             JOIN pack_sessions ps ON ps.session_id = s.id
             WHERE ps.pack_id = :pid AND s.deleted_at IS NULL
             ORDER BY s.session_date ASC, s.start_time ASC"
        );
        $stmt->execute([':pid' => $packId]);
        return $stmt->fetchAll();
    }

    /**
     * Returns all non-deleted packs that contain the given session.
     */
    public function getPacksForSession(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*
             FROM packs p
             JOIN pack_sessions ps ON ps.pack_id = p.id
             WHERE ps.session_id = :sid AND p.deleted_at IS NULL
             ORDER BY p.title ASC"
        );
        $stmt->execute([':sid' => $sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Returns true when every session in the pack has at least one remaining
     * seat and is not cancelled.
     */
    public function isAvailable(int $packId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN s.remaining_seats > 0 AND s.status != 'cancelled' THEN 1 ELSE 0 END) AS available
             FROM sessions s
             JOIN pack_sessions ps ON ps.session_id = s.id
             WHERE ps.pack_id = :pid AND s.deleted_at IS NULL"
        );
        $stmt->execute([':pid' => $packId]);
        $row = $stmt->fetch();
        $total     = (int) ($row['total']     ?? 0);
        $available = (int) ($row['available'] ?? 0);

        return $total > 0 && $available === $total;
    }

    /**
     * Returns all non-deleted packs that contain the given session,
     * each annotated with an `is_available` flag (1/0).
     * Uses a single query to avoid N+1 availability checks.
     */
    public function getPacksForSessionWithAvailability(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM sessions s2
                     JOIN pack_sessions ps2 ON ps2.session_id = s2.id
                     WHERE ps2.pack_id = p.id AND s2.deleted_at IS NULL) AS session_count,
                    (SELECT COUNT(*) FROM sessions s3
                     JOIN pack_sessions ps3 ON ps3.session_id = s3.id
                     WHERE ps3.pack_id = p.id AND s3.deleted_at IS NULL
                       AND s3.remaining_seats > 0 AND s3.status != 'cancelled') AS available_count
             FROM packs p
             JOIN pack_sessions ps ON ps.pack_id = p.id
             WHERE ps.session_id = :sid AND p.deleted_at IS NULL
             ORDER BY p.title ASC"
        );
        $stmt->execute([':sid' => $sessionId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $total     = (int) $row['session_count'];
            $available = (int) $row['available_count'];
            $row['is_available'] = ($total > 0 && $available === $total) ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    // ── Mutations ─────────────────────────────────────────────────────────────

    /**
     * Creates a pack and links the given session IDs to it.
     * Returns the new pack's ID.
     */
    public function create(array $data, array $sessionIds): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO packs (title, description, price_cents)
             VALUES (:title, :description, :price_cents)
             RETURNING id'
        );
        $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'] ?? null,
            ':price_cents' => (int) $data['price_cents'],
        ]);
        $id = (int) $stmt->fetchColumn();

        $this->setSessions($id, $sessionIds);

        return $id;
    }

    /**
     * Updates an existing pack's fields and re-syncs its session list.
     */
    public function update(int $id, array $data, array $sessionIds): void
    {
        $stmt = $this->db->prepare(
            'UPDATE packs
             SET title = :title,
                 description = :description,
                 price_cents = :price_cents
             WHERE id = :id'
        );
        $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'] ?? null,
            ':price_cents' => (int) $data['price_cents'],
            ':id'          => $id,
        ]);

        $this->setSessions($id, $sessionIds);
    }

    /**
     * Soft-deletes a pack.
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE packs SET deleted_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Replaces the full session list for a pack.
     */
    private function setSessions(int $packId, array $sessionIds): void
    {
        // Remove existing links
        $stmt = $this->db->prepare('DELETE FROM pack_sessions WHERE pack_id = :pid');
        $stmt->execute([':pid' => $packId]);

        // Insert new links
        $insert = $this->db->prepare(
            'INSERT INTO pack_sessions (pack_id, session_id) VALUES (:pid, :sid)'
        );
        foreach ($sessionIds as $sid) {
            $insert->execute([':pid' => $packId, ':sid' => (int) $sid]);
        }
    }
}
