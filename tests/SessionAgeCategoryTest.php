<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for age_category support in SessionModel.
 */
#[RunTestsInSeparateProcesses]
class SessionAgeCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../src/Database.php';
        require_once __DIR__ . '/../src/SessionModel.php';
    }

    /** Inject a mock PDO into the Database singleton. */
    private function injectPdo(PDO $pdo): void
    {
        $ref = new ReflectionProperty(Database::class, 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, $pdo);
    }

    // -------------------------------------------------------------------------
    // SessionModel::create() includes age_category
    // -------------------------------------------------------------------------

    public function testCreateIncludesAgeCategoryColumn(): void
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

        $model = new SessionModel();
        $model->create([
            'title'         => 'Test',
            'theme'         => 'Pâtisserie',
            'session_date'  => '2026-03-01',
            'start_time'    => '10:00',
            'end_time'      => '12:00',
            'max_attendees' => 8,
            'price_cents'   => 1500,
            'age_category'  => '3-5',
        ]);

        $this->assertStringContainsString('age_category', $capturedSql);
    }

    public function testCreateDefaultsAgeCategoryTo6To12(): void
    {
        $capturedParams = [];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')
            ->willReturnCallback(function (array $params) use (&$capturedParams) {
                $capturedParams = $params;
                return true;
            });
        $stmt->method('fetchColumn')->willReturn(1);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->injectPdo($pdo);

        $model = new SessionModel();
        $model->create([
            'title'         => 'Test',
            'theme'         => 'Pâtisserie',
            'session_date'  => '2026-03-01',
            'start_time'    => '10:00',
            'end_time'      => '12:00',
            'max_attendees' => 8,
            'price_cents'   => 1500,
        ]);

        $this->assertSame('6-12', $capturedParams[':age_category']);
    }

    // -------------------------------------------------------------------------
    // SessionModel::update() includes age_category
    // -------------------------------------------------------------------------

    public function testUpdateIncludesAgeCategoryColumn(): void
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
        $model->update(1, [
            'title'         => 'Test',
            'theme'         => 'Pâtisserie',
            'session_date'  => '2026-03-01',
            'start_time'    => '10:00',
            'end_time'      => '12:00',
            'max_attendees' => 8,
            'price_cents'   => 1500,
            'age_category'  => '13+',
        ]);

        $this->assertStringContainsString('age_category', $capturedSql);
    }

    // -------------------------------------------------------------------------
    // SessionModel::getUpcoming() selects age_category
    // -------------------------------------------------------------------------

    public function testGetUpcomingSelectsAgeCategory(): void
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

        $this->assertStringContainsString('age_category', $capturedSql);
    }
}
