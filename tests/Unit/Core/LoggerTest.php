<?php

declare(strict_types=1);

use App\Core\Config\Env;
use App\Core\Support\Logger;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    private string $testLogDir;
    private string $logFile;
    private ?string $previousEnvLogDir = null;
    private ?string $previousServerLogDir = null;
    private string|false $previousPutenvLogDir = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testLogDir = ROOT . '/tests/tmp-logs';
        $this->logFile = $this->testLogDir . '/app.log';

        $this->previousEnvLogDir = $_ENV['LOG_DIR'] ?? null;
        $this->previousServerLogDir = $_SERVER['LOG_DIR'] ?? null;
        $this->previousPutenvLogDir = getenv('LOG_DIR');

        $this->removeTestLogDirectory();

        $_ENV['LOG_DIR'] = $this->testLogDir;
        $_SERVER['LOG_DIR'] = $this->testLogDir;
        putenv('LOG_DIR=' . $this->testLogDir);

        Env::clear();
    }

    protected function tearDown(): void
    {
        $this->removeTestLogDirectory();

        if ($this->previousEnvLogDir !== null)
        {
            $_ENV['LOG_DIR'] = $this->previousEnvLogDir;
        }
        else
        {
            unset($_ENV['LOG_DIR']);
        }

        if ($this->previousServerLogDir !== null)
        {
            $_SERVER['LOG_DIR'] = $this->previousServerLogDir;
        }
        else
        {
            unset($_SERVER['LOG_DIR']);
        }

        if ($this->previousPutenvLogDir !== false)
        {
            putenv('LOG_DIR=' . $this->previousPutenvLogDir);
        }
        else
        {
            putenv('LOG_DIR');
        }

        Env::clear();

        parent::tearDown();
    }

    public function testErrorCreatesLogFileAndWritesMessage(): void
    {
        Logger::error('Erreur test');

        $this->assertFileExists($this->logFile);

        $content = file_get_contents($this->logFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('[ERROR]', $content);
        $this->assertStringContainsString('Erreur test', $content);
    }

    public function testWarningWritesCorrectLevel(): void
    {
        Logger::warning('Attention');

        $this->assertFileExists($this->logFile);

        $content = file_get_contents($this->logFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('[WARNING]', $content);
        $this->assertStringContainsString('Attention', $content);
    }

    public function testInfoWritesCorrectLevel(): void
    {
        Logger::info('Information');

        $this->assertFileExists($this->logFile);

        $content = file_get_contents($this->logFile);

        $this->assertIsString($content);
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

        $this->assertFileExists($this->logFile);

        $content = file_get_contents($this->logFile);

        $this->assertIsString($content);
        $this->assertStringContainsString('Message 1', $content);
        $this->assertStringContainsString('Message 2', $content);
    }

    public function testLogLineContainsFormattedDateAndLevel(): void
    {
        Logger::error('Date format test');

        $content = file_get_contents($this->logFile);

        $this->assertIsString($content);
        $this->assertMatchesRegularExpression(
            '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \[ERROR\] Date format test\r?\n$/',
            $content
        );
    }

    private function removeTestLogDirectory(): void
    {
        if (file_exists($this->logFile))
        {
            unlink($this->logFile);
        }

        if (is_dir($this->testLogDir))
        {
            rmdir($this->testLogDir);
        }
    }
}