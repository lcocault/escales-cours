<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Mailer::sendRegistrationNotificationToAdmin().
 */
#[RunTestsInSeparateProcesses]
class MailerRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        // Define constants required by Mailer
        if (!defined('ADMIN_EMAIL')) {
            define('ADMIN_EMAIL', 'admin@example.com');
        }
        if (!defined('SMTP_FROM')) {
            define('SMTP_FROM', 'noreply@example.com');
        }
        if (!defined('SMTP_FROM_NAME')) {
            define('SMTP_FROM_NAME', 'Escales Culinaires');
        }
        if (!defined('APP_BASE_URL')) {
            define('APP_BASE_URL', 'https://example.com');
        }

        require_once __DIR__ . '/../src/Mailer.php';
    }

    /** Access a private static method via reflection. */
    private function callPrivate(string $method, array $args): string
    {
        $ref = new ReflectionMethod(Mailer::class, $method);
        $ref->setAccessible(true);
        return $ref->invoke(null, ...$args);
    }

    // -------------------------------------------------------------------------
    // adminRegistrationBody()
    // -------------------------------------------------------------------------

    public function testBodyContainsUserName(): void
    {
        $user = [
            'first_name' => 'Alice',
            'last_name'  => 'Dupont',
            'email'      => 'alice@example.com',
        ];

        $body = $this->callPrivate('adminRegistrationBody', [$user]);

        $this->assertStringContainsString('Alice', $body);
        $this->assertStringContainsString('Dupont', $body);
    }

    public function testBodyContainsUserEmail(): void
    {
        $user = [
            'first_name' => 'Bob',
            'last_name'  => 'Martin',
            'email'      => 'bob@example.com',
        ];

        $body = $this->callPrivate('adminRegistrationBody', [$user]);

        $this->assertStringContainsString('bob@example.com', $body);
    }

    public function testBodyContainsAdminLink(): void
    {
        $user = [
            'first_name' => 'Clara',
            'last_name'  => 'Durand',
            'email'      => 'clara@example.com',
        ];

        $body = $this->callPrivate('adminRegistrationBody', [$user]);

        $this->assertStringContainsString(APP_BASE_URL . '/admin/index.php', $body);
    }

    public function testBodyEscapesHtmlInUserName(): void
    {
        $user = [
            'first_name' => '<script>alert(1)</script>',
            'last_name'  => 'Doe',
            'email'      => 'xss@example.com',
        ];

        $body = $this->callPrivate('adminRegistrationBody', [$user]);

        $this->assertStringNotContainsString('<script>', $body);
        $this->assertStringContainsString('&lt;script&gt;', $body);
    }
}
