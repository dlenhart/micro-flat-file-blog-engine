<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Log\Logger;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the PSR-3 file logger introduced in step 4.
 */
final class LoggerTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = sys_get_temp_dir() . '/microblog-test-' . uniqid() . '.log';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            @chmod($this->logFile, 0644);
            unlink($this->logFile);
        }
    }

    public function testWritesSeverityAndMessage(): void
    {
        $logger = new Logger($this->logFile);
        $logger->error('Something broke');
        unset($logger); // close handle so the buffer flushes to disk

        $contents = (string) file_get_contents($this->logFile);

        $this->assertStringContainsString('[ERROR]', $contents);
        $this->assertStringContainsString('Something broke', $contents);
    }

    public function testInterpolatesContextPlaceholders(): void
    {
        $logger = new Logger($this->logFile);
        $logger->warning('Missing {resource} for user {id}', ['resource' => 'post', 'id' => 42]);
        unset($logger);

        $contents = (string) file_get_contents($this->logFile);

        $this->assertStringContainsString('[WARNING]', $contents);
        $this->assertStringContainsString('Missing post for user 42', $contents);
    }

    public function testThrowsWhenLogFileIsNotWritable(): void
    {
        touch($this->logFile);
        chmod($this->logFile, 0444);

        try {
            $this->expectException(\RuntimeException::class);
            new Logger($this->logFile);
        } finally {
            chmod($this->logFile, 0644); // let tearDown remove it
        }
    }
}
