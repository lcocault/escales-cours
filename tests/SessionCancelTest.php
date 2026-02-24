<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the admin session-cancel flow:
 * - SessionModel::cancelSession() sets status to 'cancelled'
 * - BookingModel::cancel() updates booking status
 * - PaymentService::isRealPaymentRef() correctly identifies real payments
 * - SessionModel::getUpcoming() excludes cancelled sessions
 */
#[RunTestsInSeparateProcesses]
class SessionCancelTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../src/Database.php';
        require_once __DIR__ . '/../src/SessionModel.php';
        require_once __DIR__ . '/../src/BookingModel.php';
        require_once __DIR__ . '/../src/PaymentService.php';
    }

    /** Inject a mock PDO into the Database singleton. */
    private function injectPdo(PDO $pdo): void
    {
        $ref = new ReflectionProperty(Database::class, 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $pdo);
    }

    // -------------------------------------------------------------------------
    // SessionModel::cancelSession()
    // -------------------------------------------------------------------------

    public function testCancelSessionSetsStatusToCancelled(): void
    {
        $capturedSql = '';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $stmt) {
                $capturedSql = $sql;
                return $stmt;
            });

        $this->injectPdo($pdo);

        $model = new SessionModel();
        $model->cancelSession(7);

        $this->assertStringContainsString("'cancelled'", $capturedSql);
        $this->assertStringContainsString('UPDATE sessions SET status', $capturedSql);
    }

    // -------------------------------------------------------------------------
    // SessionModel::getUpcoming() excludes cancelled sessions
    // -------------------------------------------------------------------------

    public function testGetUpcomingExcludesCancelledSessions(): void
    {
        $capturedSql = '';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $stmt) {
                $capturedSql = $sql;
                return $stmt;
            });

        $this->injectPdo($pdo);

        $model = new SessionModel();
        $model->getUpcoming();

        $this->assertStringContainsString("status != 'cancelled'", $capturedSql);
    }

    // -------------------------------------------------------------------------
    // BookingModel::cancel()
    // -------------------------------------------------------------------------

    public function testCancelBookingSetsStatusToCancelled(): void
    {
        $capturedSql = '';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $stmt) {
                $capturedSql = $sql;
                return $stmt;
            });

        $this->injectPdo($pdo);

        $model = new BookingModel();
        $model->cancel(42);

        $this->assertStringContainsString("'cancelled'", $capturedSql);
        $this->assertStringContainsString('UPDATE bookings SET status', $capturedSql);
    }

    // -------------------------------------------------------------------------
    // PaymentService::isRealPaymentRef()
    // -------------------------------------------------------------------------

    public function testIsRealPaymentRefReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(PaymentService::isRealPaymentRef(''));
    }

    public function testIsRealPaymentRefReturnsFalseForCredit(): void
    {
        $this->assertFalse(PaymentService::isRealPaymentRef('credit'));
    }

    public function testIsRealPaymentRefReturnsFalseForDemoPrefix(): void
    {
        $this->assertFalse(PaymentService::isRealPaymentRef('demo_abc123'));
    }

    public function testIsRealPaymentRefReturnsFalseForPaidPrefix(): void
    {
        $this->assertFalse(PaymentService::isRealPaymentRef('paid_abc123'));
    }

    public function testIsRealPaymentRefReturnsTrueForRealStripeId(): void
    {
        $this->assertTrue(PaymentService::isRealPaymentRef('pi_3NxYj2CZ12345678'));
    }
}
