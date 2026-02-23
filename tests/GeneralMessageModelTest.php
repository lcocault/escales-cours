<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for GeneralMessageModel.
 *
 * Each test runs in its own process so that the Database singleton can be
 * replaced with a mock without state leaking between tests.
 */
#[RunTestsInSeparateProcesses]
class GeneralMessageModelTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../src/Database.php';
        require_once __DIR__ . '/../src/GeneralMessageModel.php';
    }

    /** Inject a mock PDO into the Database singleton. */
    private function injectPdo(PDO $pdo): void
    {
        $ref = new ReflectionProperty(Database::class, 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $pdo);
    }

    // -------------------------------------------------------------------------
    // Static helper
    // -------------------------------------------------------------------------

    public function testTypesReturnsExpectedValues(): void
    {
        $types = GeneralMessageModel::types();

        $this->assertContains('info',    $types);
        $this->assertContains('warning', $types);
        $this->assertContains('danger',  $types);
        $this->assertContains('success', $types);
        $this->assertCount(4, $types);
    }

    // -------------------------------------------------------------------------
    // getAll()
    // -------------------------------------------------------------------------

    public function testGetAllReturnsRows(): void
    {
        $rows = [
            ['id' => 1, 'body' => 'Hello', 'type' => 'info', 'created_at' => '2026-02-23 10:00:00+00'],
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn($rows);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model  = new GeneralMessageModel();
        $result = $model->getAll();

        $this->assertCount(1, $result);
        $this->assertSame('Hello', $result[0]['body']);
    }

    // -------------------------------------------------------------------------
    // findById()
    // -------------------------------------------------------------------------

    public function testFindByIdReturnsRowWhenFound(): void
    {
        $row = ['id' => 5, 'body' => 'Test', 'type' => 'warning', 'created_at' => '2026-02-23 10:00:00+00'];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($row);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model  = new GeneralMessageModel();
        $result = $model->findById(5);

        $this->assertNotNull($result);
        $this->assertSame(5, $result['id']);
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model  = new GeneralMessageModel();
        $result = $model->findById(999);

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function testCreateReturnsNewId(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn('42');

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new GeneralMessageModel();
        $id    = $model->create('Attention, séance annulée.', 'warning');

        $this->assertSame(42, $id);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    public function testUpdateExecutesWithoutException(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new GeneralMessageModel();
        $model->update(1, 'Nouveau texte', 'success');

        $this->addToAssertionCount(1);
    }

    // -------------------------------------------------------------------------
    // softDelete()
    // -------------------------------------------------------------------------

    public function testSoftDeleteExecutesWithoutException(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new GeneralMessageModel();
        $model->softDelete(3);

        $this->addToAssertionCount(1);
    }
}
