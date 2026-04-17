<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Functions;

final class FunctionsTest extends TestCase
{
    private array $serverBackup = [];
    private array $envBackup = [];
    private array $filesBackup = [];
    private string|false $appBasePathBackup = false;
    private string|false $appNameBackup = false;
    private string|false $appEnvBackup = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup = $_SERVER;
        $this->envBackup = $_ENV;
        $this->filesBackup = $_FILES;

        $this->appBasePathBackup = getenv('APP_BASE_PATH');
        $this->appNameBackup = getenv('APP_NAME');
        $this->appEnvBackup = getenv('APP_ENV');

        $_SERVER = [];
        $_ENV = [];
        $_FILES = [];

        putenv('APP_BASE_PATH');
        putenv('APP_NAME');
        putenv('APP_ENV');

        Functions::clearEnvCache();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_ENV = $this->envBackup;
        $_FILES = $this->filesBackup;

        $this->restoreEnvVar('APP_BASE_PATH', $this->appBasePathBackup);
        $this->restoreEnvVar('APP_NAME', $this->appNameBackup);
        $this->restoreEnvVar('APP_ENV', $this->appEnvBackup);

        Functions::clearEnvCache();

        parent::tearDown();
    }

    private function restoreEnvVar(string $key, string|false $value): void
    {
        if ($value === false)
        {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
            return;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    public function testEnvReturnsDefaultWhenKeyDoesNotExist(): void
    {
        $this->assertSame('fallback', Functions::env('KEY_INEXISTANTE_TEST', 'fallback'));
    }

    public function testEnvReturnsValueFromEnvArray(): void
    {
        $_ENV['APP_NAME'] = 'LoliSSR';
        Functions::clearEnvCache();

        $this->assertSame('LoliSSR', Functions::env('APP_NAME', 'fallback'));
    }

    public function testEnvReturnsValueFromServerArray(): void
    {
        $_SERVER['APP_ENV'] = 'local';
        Functions::clearEnvCache();

        $this->assertSame('local', Functions::env('APP_ENV', 'prod'));
    }

    public function testBasePathReturnsDefaultSlash(): void
    {
        Functions::clearEnvCache();

        $this->assertSame('/', Functions::basePath());
    }

    public function testBasePathReturnsConfiguredPath(): void
    {
        $_ENV['APP_BASE_PATH'] = '/lolissr/';
        Functions::clearEnvCache();

        $this->assertSame('/lolissr/', Functions::basePath());
    }

    public function testSiteNameReturnsConfiguredAppName(): void
    {
        $_ENV['APP_NAME'] = 'LoliSSR';
        Functions::clearEnvCache();

        $this->assertSame('LoliSSR', Functions::siteName());
    }

    public function testIsPostReturnsTrueWhenRequestMethodIsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->assertTrue(Functions::isPost());
    }

    public function testIsPostReturnsFalseWhenRequestMethodIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertFalse(Functions::isPost());
    }

    public function testFileExistsReturnsTrueWhenFileIsPresent(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertTrue(Functions::fileExists('image'));
    }

    public function testFileExistsReturnsFalseWhenFileIsMissing(): void
    {
        $this->assertFalse(Functions::fileExists('image'));
    }

    public function testFileErrorReturnsUploadErrorCode(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0,
        ];

        $this->assertSame(UPLOAD_ERR_NO_FILE, Functions::fileError('image'));
    }

    public function testFileExtensionReturnsLowercaseExtension(): void
    {
        $_FILES['image'] = [
            'name' => 'Cover.WEBP',
            'type' => 'image/webp',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertSame('webp', Functions::fileExtension('image'));
    }

    public function testFileTmpReturnsTmpPath(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.png',
            'type' => 'image/png',
            'tmp_name' => 'C:/wamp64/tmp/php999.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 456,
        ];

        $this->assertSame('C:/wamp64/tmp/php999.tmp', Functions::fileTmp('image'));
    }
}