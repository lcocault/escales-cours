<?php
// src/ShopProductModel.php – shop product (prepared meals) CRUD

class ShopProductModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Retrieval ─────────────────────────────────────────────────────────────

    /** Returns all non-deleted products, newest first. */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM shop_products WHERE deleted_at IS NULL ORDER BY created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /** Returns only available (non-deleted, is_available=true) products. */
    public function getAvailable(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM shop_products WHERE deleted_at IS NULL AND is_available = TRUE ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    /** Returns a single product or null if not found / soft-deleted. */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM shop_products WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Mutations ─────────────────────────────────────────────────────────────

    /** Creates a product and returns its new ID. */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO shop_products (name, description, photo_filename, price_cents, is_available)
             VALUES (:name, :description, :photo, :price, :available)
             RETURNING id'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':photo'       => $data['photo_filename'] ?? null,
            ':price'       => (int) $data['price_cents'],
            ':available'   => ($data['is_available'] ?? true) ? 'TRUE' : 'FALSE',
        ]);
        return (int) $stmt->fetchColumn();
    }

    /** Updates a product's editable fields. */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE shop_products
             SET name = :name,
                 description = :description,
                 price_cents = :price,
                 is_available = :available
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'      => $data['name'],
            ':description' => $data['description'] ?? null,
            ':price'     => (int) $data['price_cents'],
            ':available' => ($data['is_available'] ?? true) ? 'TRUE' : 'FALSE',
            ':id'        => $id,
        ]);
    }

    /** Updates only the photo filename for a product. */
    public function updatePhoto(int $id, string $filename): void
    {
        $stmt = $this->db->prepare(
            'UPDATE shop_products SET photo_filename = :photo WHERE id = :id'
        );
        $stmt->execute([':photo' => $filename, ':id' => $id]);
    }

    /** Soft-deletes a product. */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE shop_products SET deleted_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }
}
