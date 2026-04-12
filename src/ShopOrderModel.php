<?php
// src/ShopOrderModel.php – shop order CRUD

class ShopOrderModel
{
    private PDO $db;

    /** Delivery fee in cents for home delivery. */
    public const HOME_DELIVERY_FEE_CENTS = 500;

    /** Minimum number of days before delivery date when ordering. */
    public const MIN_DAYS_BEFORE_DELIVERY = 2;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Retrieval ─────────────────────────────────────────────────────────────

    /** Returns all orders with user info, newest first. */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT o.*, u.first_name, u.last_name, u.email
             FROM shop_orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /** Returns all orders for a user, newest first. */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM shop_orders WHERE user_id = :uid ORDER BY created_at DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** Returns a single order or null if not found. */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM shop_orders WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Returns all items for an order, with product info. */
    public function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, p.photo_filename
             FROM shop_order_items i
             LEFT JOIN shop_products p ON p.id = i.product_id
             WHERE i.order_id = :oid
             ORDER BY i.id ASC'
        );
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll();
    }

    // ── Mutations ─────────────────────────────────────────────────────────────

    /**
     * Creates a new order with items and returns the order ID.
     *
     * @param int    $userId
     * @param string $deliveryMethod  'home'|'market_wednesday'|'market_friday'|'shop'
     * @param string $deliveryDate    'Y-m-d'
     * @param string $deliveryAddress Only required when deliveryMethod='home'
     * @param array  $items           Array of ['product_id', 'product_name', 'unit_price_cents', 'quantity']
     * @return int  New order ID
     */
    public function create(
        int $userId,
        string $deliveryMethod,
        string $deliveryDate,
        string $deliveryAddress,
        array $items
    ): int {
        $deliveryFeeCents = ($deliveryMethod === 'home') ? self::HOME_DELIVERY_FEE_CENTS : 0;

        $itemsTotal = 0;
        foreach ($items as $item) {
            $itemsTotal += (int) $item['unit_price_cents'] * (int) $item['quantity'];
        }
        $totalCents = $itemsTotal + $deliveryFeeCents;

        $stmt = $this->db->prepare(
            'INSERT INTO shop_orders
                 (user_id, delivery_method, delivery_date, delivery_address, delivery_fee_cents, total_cents)
             VALUES (:uid, :method, :date, :address, :fee, :total)
             RETURNING id'
        );
        $stmt->execute([
            ':uid'     => $userId,
            ':method'  => $deliveryMethod,
            ':date'    => $deliveryDate,
            ':address' => $deliveryAddress !== '' ? $deliveryAddress : null,
            ':fee'     => $deliveryFeeCents,
            ':total'   => $totalCents,
        ]);
        $orderId = (int) $stmt->fetchColumn();

        $insert = $this->db->prepare(
            'INSERT INTO shop_order_items (order_id, product_id, product_name, unit_price_cents, quantity)
             VALUES (:oid, :pid, :pname, :price, :qty)'
        );
        foreach ($items as $item) {
            $insert->execute([
                ':oid'   => $orderId,
                ':pid'   => $item['product_id'] ?? null,
                ':pname' => $item['product_name'],
                ':price' => (int) $item['unit_price_cents'],
                ':qty'   => (int) $item['quantity'],
            ]);
        }

        return $orderId;
    }

    /** Marks an order as paid and stores the payment reference. */
    public function markPaid(int $orderId, string $paymentIntentId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE shop_orders
             SET status = 'paid', payment_intent_id = :pi, paid_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([':pi' => $paymentIntentId, ':id' => $orderId]);
    }

    /** Advances an order to 'prepared'. */
    public function markPrepared(int $orderId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE shop_orders SET status = 'prepared' WHERE id = :id"
        );
        $stmt->execute([':id' => $orderId]);
    }

    /** Advances an order to 'delivered'. */
    public function markDelivered(int $orderId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE shop_orders SET status = 'delivered' WHERE id = :id"
        );
        $stmt->execute([':id' => $orderId]);
    }

    /** Marks an order as 'cancelled'. */
    public function cancel(int $orderId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE shop_orders SET status = 'cancelled' WHERE id = :id"
        );
        $stmt->execute([':id' => $orderId]);
    }

    /** Deletes a pending order (and its items via cascade) – used on payment cancel. */
    public function deletePending(int $orderId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM shop_orders WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute([':id' => $orderId]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Returns the next available date (as 'Y-m-d') for a given delivery method
     * that is at least MIN_DAYS_BEFORE_DELIVERY days from today.
     *
     * For market_wednesday / market_friday, it returns the next Wednesday or
     * Friday that satisfies the constraint.
     * For home / shop, it just returns today + MIN_DAYS_BEFORE_DELIVERY.
     */
    public static function nextAvailableDate(string $deliveryMethod): string
    {
        $minTs = strtotime('+' . self::MIN_DAYS_BEFORE_DELIVERY . ' days');
        switch ($deliveryMethod) {
            case 'market_wednesday':
                return self::nextWeekday($minTs, 3); // 3 = Wednesday
            case 'market_friday':
                return self::nextWeekday($minTs, 5); // 5 = Friday
            default:
                return date('Y-m-d', $minTs);
        }
    }

    /**
     * Returns the earliest date string >= $fromTs that falls on $targetDow (0=Sun … 6=Sat).
     * When $fromTs already falls on $targetDow (diff = 0), that same day is returned because
     * $fromTs already satisfies the minimum-days constraint computed by the caller.
     */
    private static function nextWeekday(int $fromTs, int $targetDow): string
    {
        $dow  = (int) date('w', $fromTs);
        $diff = ($targetDow - $dow + 7) % 7;
        return date('Y-m-d', $fromTs + $diff * 86400);
    }

    /**
     * Validates that a delivery date string is:
     *  – a valid date
     *  – at least MIN_DAYS_BEFORE_DELIVERY days from today
     *  – on the correct weekday for market methods
     */
    public static function validateDeliveryDate(string $deliveryMethod, string $dateStr): bool
    {
        $ts = strtotime($dateStr);
        if ($ts === false) {
            return false;
        }
        $minTs = strtotime('+' . self::MIN_DAYS_BEFORE_DELIVERY . ' days midnight');
        if ($ts < $minTs) {
            return false;
        }
        if ($deliveryMethod === 'market_wednesday' && (int) date('w', $ts) !== 3) {
            return false;
        }
        if ($deliveryMethod === 'market_friday' && (int) date('w', $ts) !== 5) {
            return false;
        }
        return true;
    }
}
