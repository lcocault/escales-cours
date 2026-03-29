<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PackModel.
 */
#[RunTestsInSeparateProcesses]
class PackModelTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../src/Database.php';
        require_once __DIR__ . '/../src/PackModel.php';
    }

    /** Inject a mock PDO into the Database singleton. */
    private function injectPdo(PDO $pdo): void
    {
        $ref = new ReflectionProperty(Database::class, 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $pdo);
    }

    // -------------------------------------------------------------------------
    // create – INSERT SQL contains required columns
    // -------------------------------------------------------------------------

    public function testCreateInsertsPackRow(): void
    {
        $capturedInsertSql = '';

        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true);
        $stmtInsert->method('fetchColumn')->willReturn(42);

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);

        $stmtLink = $this->createMock(PDOStatement::class);
        $stmtLink->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')
            ->willReturnCallback(
                function (string $sql) use (
                    &$capturedInsertSql,
                    $stmtInsert,
                    $stmtDelete,
                    $stmtLink
                ) {
                    if (stripos($sql, 'INSERT INTO packs') !== false) {
                        $capturedInsertSql = $sql;
                        return $stmtInsert;
                    }
                    if (stripos($sql, 'DELETE FROM pack_sessions') !== false) {
                        return $stmtDelete;
                    }
                    return $stmtLink;
                }
            );

        $this->injectPdo($pdo);

        $model = new PackModel();
        $id = $model->create(
            ['title' => 'Mon pack', 'description' => 'Desc', 'price_cents' => 5000],
            [1, 2]
        );

        $this->assertSame(42, $id);
        $this->assertStringContainsStringIgnoringCase('INSERT INTO packs', $capturedInsertSql);
        $this->assertStringContainsString(':title', $capturedInsertSql);
        $this->assertStringContainsString(':price_cents', $capturedInsertSql);
    }

    // -------------------------------------------------------------------------
    // isAvailable – returns false when no sessions are linked
    // -------------------------------------------------------------------------

    public function testIsAvailableReturnsFalseWhenNoSessions(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(['total' => 0, 'available' => 0]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new PackModel();
        $this->assertFalse($model->isAvailable(1));
    }

    // -------------------------------------------------------------------------
    // isAvailable – returns true when all sessions are available
    // -------------------------------------------------------------------------

    public function testIsAvailableReturnsTrueWhenAllSessionsAvailable(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(['total' => 3, 'available' => 3]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new PackModel();
        $this->assertTrue($model->isAvailable(1));
    }

    // -------------------------------------------------------------------------
    // isAvailable – returns false when only some sessions are available
    // -------------------------------------------------------------------------

    public function testIsAvailableReturnsFalseWhenSomeSessionsFull(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(['total' => 3, 'available' => 2]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new PackModel();
        $this->assertFalse($model->isAvailable(1));
    }

    // -------------------------------------------------------------------------
    // delete – issues an UPDATE … SET deleted_at = NOW()
    // -------------------------------------------------------------------------

    public function testDeleteSetsDeletedAt(): void
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

        $model = new PackModel();
        $model->delete(7);

        $this->assertStringContainsStringIgnoringCase('UPDATE packs', $capturedSql);
        $this->assertStringContainsStringIgnoringCase('deleted_at', $capturedSql);
    }

    // -------------------------------------------------------------------------
    // findById – returns null when PDO returns no row
    // -------------------------------------------------------------------------

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new PackModel();
        $this->assertNull($model->findById(99));
    }

    // -------------------------------------------------------------------------
    // getPacksForSessionWithAvailability – annotates rows with is_available flag
    // -------------------------------------------------------------------------

    public function testGetPacksForSessionWithAvailabilityAnnotatesRows(): void
    {
        $fakeRows = [
            ['id' => 1, 'title' => 'Pack A', 'price_cents' => 5000, 'session_count' => 2, 'available_count' => 2],
            ['id' => 2, 'title' => 'Pack B', 'price_cents' => 3000, 'session_count' => 3, 'available_count' => 2],
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($fakeRows);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model  = new PackModel();
        $result = $model->getPacksForSessionWithAvailability(5);

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['is_available']); // 2 total, 2 available → available
        $this->assertSame(0, $result[1]['is_available']); // 3 total, 2 available → not available
    }
}
