<?php

declare(strict_types=1);

use App\Core\Logger;
use App\Core\Functions;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    private string $testLogDir;
    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testLogDir = ROOT . '/tests/tmp-logs';
        $this->logFile = $this->testLogDir . '/app.log';

        // Configure LOG_DIR
        $_ENV['LOG_DIR'] = $this->testLogDir;
        $_SERVER['LOG_DIR'] = $this->testLogDir;

        Functions::clearEnvCache();

        // Nettoyage avant test
        if (file_exists($this->logFile))
        {
            unlink($this->logFile);
        }

        if (is_dir($this->testLogDir))
        {
            rmdir($this->testLogDir);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile))
        {
            unlink($this->logFile);
        }

        if (is_dir($this->testLogDir))
        {
            rmdir($this->testLogDir);
        }

        unset($_ENV['LOG_DIR'], $_SERVER['LOG_DIR']);

        Functions::clearEnvCache();

        parent::tearDown();
    }

    public function testErrorCreatesLogFileAndWritesMessage(): void
    {
        Logger::error('Erreur test');

        $this->assertFileExists($this->logFile);

        $content = file_get_contents($this->logFile);

        $this->assertStringContainsString('[ERROR]', $content);
        $this->assertStringContainsString('Erreur test', $content);
    }

    public function testWarningWritesCorrectLevel(): void
    {
        Logger::warning('Attention');

        $content = file_get_contents($this->logFile);

        $this->assertStringContainsString('[WARNING]', $content);
        $this->assertStringContainsString('Attention', $content);
    }

    public function testInfoWritesCorrectLevel(): void
    {
        Logger::info('Information');

        $content = file_get_contents($this->logFile);

        $this->assertStringContainsString('[INFO]', $content);
        $this->assertStringContainsString('Information', $content);
    }

    public function testLoggerCreatesDirectoryIfMissing(): void
    {
        $this->assertDirectoryDoesNotExist($this->testLogDir);

        Logger::info('Création dossier');

        $this->assertDirectoryExists($this->testLogDir);
        $this->assertFileExists($this->logFile);
    }

    public function testMultipleWritesAppendContent(): void
    {
        Logger::info('Message 1');
        Logger::info('Message 2');

        $content = file_get_contents($this->logFile);

        $this->assertStringContainsString('Message 1', $content);
        $this->assertStringContainsString('Message 2', $content);
    }
}