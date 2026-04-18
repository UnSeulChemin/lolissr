<?php

declare(strict_types=1);

use App\Core\Functions;
use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    private array $serverBackup = [];
    private array $envBackup = [];
    private array $filesBackup = [];
    private array $postBackup = [];

    private string|false $appBasePathBackup = false;
    private string|false $appNameBackup = false;
    private string|false $appEnvBackup = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup = $_SERVER;
        $this->envBackup = $_ENV;
        $this->filesBackup = $_FILES;
        $this->postBackup = $_POST;

        $this->appBasePathBackup = getenv('APP_BASE_PATH');
        $this->appNameBackup = getenv('APP_NAME');
        $this->appEnvBackup = getenv('APP_ENV');

        $_SERVER = [];
        $_ENV = [];
        $_FILES = [];
        $_POST = [];

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
        $_POST = $this->postBackup;

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

    public function testPostStringReturnsTrimmedValue(): void
    {
        $_POST['livre'] = '  One Piece  ';

        $this->assertSame('One Piece', Functions::postString('livre'));
    }

    public function testPostStringReturnsEmptyStringWhenMissing(): void
    {
        $this->assertSame('', Functions::postString('livre'));
    }

    public function testPostIntReturnsIntegerValue(): void
    {
        $_POST['numero'] = '12';

        $this->assertSame(12, Functions::postInt('numero'));
    }

    public function testPostIntReturnsZeroWhenMissing(): void
    {
        $this->assertSame(0, Functions::postInt('numero'));
    }

    public function testPostNullableStringReturnsNullWhenMissing(): void
    {
        $this->assertNull(Functions::postNullableString('commentaire'));
    }

    public function testPostNullableStringReturnsNullWhenEmpty(): void
    {
        $_POST['commentaire'] = '   ';

        $this->assertNull(Functions::postNullableString('commentaire'));
    }

    public function testPostNullableStringReturnsTrimmedValue(): void
    {
        $_POST['commentaire'] = '  Très bon tome  ';

        $this->assertSame('Très bon tome', Functions::postNullableString('commentaire'));
    }

    public function testNormalizeSlugReturnsNormalizedValue(): void
    {
        $this->assertSame('one-piece', Functions::normalizeSlug('One Piece'));
    }

    public function testNormalizeSlugTrimsAndLowercasesValue(): void
    {
        $this->assertSame(
            'dragon-ball-super',
            Functions::normalizeSlug('  Dragon Ball Super  ')
        );
    }

    public function testNormalizeCommentaireReturnsNullWhenValueIsEmpty(): void
    {
        $this->assertNull(Functions::normalizeCommentaire('   '));
    }

    public function testNormalizeCommentaireReturnsTrimmedValue(): void
    {
        $this->assertSame(
            'Excellent tome',
            Functions::normalizeCommentaire('  Excellent tome  ')
        );
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

    public function testFileErrorReturnsNullWhenFileDoesNotExist(): void
    {
        $this->assertNull(Functions::fileError('image'));
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

    public function testFileExtensionReturnsNullWhenFileDoesNotExist(): void
    {
        $this->assertNull(Functions::fileExtension('image'));
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

    public function testFileTmpReturnsNullWhenFileDoesNotExist(): void
    {
        $this->assertNull(Functions::fileTmp('image'));
    }

    public function testBuildThumbnailNameReturnsExpectedFormat(): void
    {
        $this->assertSame('one-piece-01', Functions::buildThumbnailName('One Piece', 1));
    }

    public function testBuildThumbnailNamePadsNumeroWithTwoDigits(): void
    {
        $this->assertSame('naruto-09', Functions::buildThumbnailName('Naruto', 9));
    }

    public function testBuildThumbnailNameReturnsEmptyStringWhenNumeroIsInvalid(): void
    {
        $this->assertSame('', Functions::buildThumbnailName('One Piece', 0));
    }

    public function testBuildThumbnailNameReturnsEmptyStringWhenLivreIsInvalid(): void
    {
        $this->assertSame('', Functions::buildThumbnailName('', 1));
    }

    public function testUploadAllowedExtensionsReturnsArray(): void
    {
        $result = Functions::uploadAllowedExtensions();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testUploadAllowedMimeTypesReturnsArray(): void
    {
        $result = Functions::uploadAllowedMimeTypes();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testUploadMaxSizeReturnsPositiveInteger(): void
    {
        $result = Functions::uploadMaxSize();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }
}