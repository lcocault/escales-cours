<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RatingModel.
 */
#[RunTestsInSeparateProcesses]
class RatingModelTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../src/Database.php';
        require_once __DIR__ . '/../src/RatingModel.php';
    }

    /** Inject a mock PDO into the Database singleton. */
    private function injectPdo(PDO $pdo): void
    {
        $ref = new ReflectionProperty(Database::class, 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $pdo);
    }

    // -------------------------------------------------------------------------
    // RatingModel::create()
    // -------------------------------------------------------------------------

    public function testCreateInsertsCorrectColumns(): void
    {
        $capturedSql = '';

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn(1);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $stmt) {
                $capturedSql = $sql;
                return $stmt;
            });

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $id = $model->create(10, 2, 5, 4, 'Super séance !', false);

        $this->assertSame(1, $id);
        $this->assertStringContainsString('INSERT INTO ratings', $capturedSql);
        $this->assertStringContainsString('stars', $capturedSql);
        $this->assertStringContainsString('comment', $capturedSql);
        $this->assertStringContainsString('is_anonymous', $capturedSql);
    }

    public function testCreatePassesCorrectParameters(): void
    {
        $capturedParams = [];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')
            ->willReturnCallback(function (array $params) use (&$capturedParams) {
                $capturedParams = $params;
                return true;
            });
        $stmt->method('fetchColumn')->willReturn(7);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $model->create(10, 2, 5, 4, 'Super séance !', true);

        $this->assertSame(10, $capturedParams[':bid']);
        $this->assertSame(2, $capturedParams[':uid']);
        $this->assertSame(5, $capturedParams[':sid']);
        $this->assertSame(4, $capturedParams[':stars']);
        $this->assertSame('Super séance !', $capturedParams[':comment']);
        $this->assertSame('TRUE', $capturedParams[':anon']);
    }

    public function testCreateStoresNullForEmptyComment(): void
    {
        $capturedParams = [];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')
            ->willReturnCallback(function (array $params) use (&$capturedParams) {
                $capturedParams = $params;
                return true;
            });
        $stmt->method('fetchColumn')->willReturn(3);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $model->create(1, 1, 1, 5, '', false);

        $this->assertNull($capturedParams[':comment']);
    }

    // -------------------------------------------------------------------------
    // RatingModel::getAverageBySession()
    // -------------------------------------------------------------------------

    public function testGetAverageReturnsFloat(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn('4.5');

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $avg = $model->getAverageBySession(1);

        $this->assertSame(4.5, $avg);
    }

    public function testGetAverageReturnsNullWhenNoRatings(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn(null);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $avg = $model->getAverageBySession(99);

        $this->assertNull($avg);
    }

    // -------------------------------------------------------------------------
    // RatingModel::countBySession()
    // -------------------------------------------------------------------------

    public function testCountBySessionReturnsInteger(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn('3');

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $count = $model->countBySession(2);

        $this->assertSame(3, $count);
    }

    // -------------------------------------------------------------------------
    // RatingModel::findByUserAndSession()
    // -------------------------------------------------------------------------

    public function testFindByUserAndSessionReturnsNullWhenNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $result = $model->findByUserAndSession(1, 99);

        $this->assertNull($result);
    }

    public function testFindByUserAndSessionReturnsRow(): void
    {
        $row = ['id' => 5, 'stars' => 4, 'comment' => 'Bien', 'is_anonymous' => false];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($row);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new RatingModel();
        $result = $model->findByUserAndSession(1, 2);

        $this->assertSame($row, $result);
    }
}
