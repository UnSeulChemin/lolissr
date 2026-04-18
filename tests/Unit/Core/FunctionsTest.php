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
    private string|false $appDebugBackup = false;
    private string|false $appPaginationBackup = false;
    private string|false $dbHostBackup = false;
    private string|false $dbNameBackup = false;
    private string|false $dbUserBackup = false;
    private string|false $dbPassBackup = false;
    private string|false $dbCharsetBackup = false;
    private string|false $uploadMaxSizeBackup = false;
    private string|false $uploadAllowedExtBackup = false;
    private string|false $uploadAllowedMimeBackup = false;

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
        $this->appDebugBackup = getenv('APP_DEBUG');
        $this->appPaginationBackup = getenv('APP_PAGINATION');
        $this->dbHostBackup = getenv('DB_HOST');
        $this->dbNameBackup = getenv('DB_NAME');
        $this->dbUserBackup = getenv('DB_USER');
        $this->dbPassBackup = getenv('DB_PASS');
        $this->dbCharsetBackup = getenv('DB_CHARSET');
        $this->uploadMaxSizeBackup = getenv('UPLOAD_MAX_SIZE');
        $this->uploadAllowedExtBackup = getenv('UPLOAD_ALLOWED_EXT');
        $this->uploadAllowedMimeBackup = getenv('UPLOAD_ALLOWED_MIME');

        $_SERVER = [];
        $_ENV = [];
        $_FILES = [];
        $_POST = [];

        putenv('APP_BASE_PATH');
        putenv('APP_NAME');
        putenv('APP_ENV');
        putenv('APP_DEBUG');
        putenv('APP_PAGINATION');
        putenv('DB_HOST');
        putenv('DB_NAME');
        putenv('DB_USER');
        putenv('DB_PASS');
        putenv('DB_CHARSET');
        putenv('UPLOAD_MAX_SIZE');
        putenv('UPLOAD_ALLOWED_EXT');
        putenv('UPLOAD_ALLOWED_MIME');

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
        $this->restoreEnvVar('APP_DEBUG', $this->appDebugBackup);
        $this->restoreEnvVar('APP_PAGINATION', $this->appPaginationBackup);
        $this->restoreEnvVar('DB_HOST', $this->dbHostBackup);
        $this->restoreEnvVar('DB_NAME', $this->dbNameBackup);
        $this->restoreEnvVar('DB_USER', $this->dbUserBackup);
        $this->restoreEnvVar('DB_PASS', $this->dbPassBackup);
        $this->restoreEnvVar('DB_CHARSET', $this->dbCharsetBackup);
        $this->restoreEnvVar('UPLOAD_MAX_SIZE', $this->uploadMaxSizeBackup);
        $this->restoreEnvVar('UPLOAD_ALLOWED_EXT', $this->uploadAllowedExtBackup);
        $this->restoreEnvVar('UPLOAD_ALLOWED_MIME', $this->uploadAllowedMimeBackup);

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

    public function testClearEnvCacheForcesFreshRead(): void
    {
        $_ENV['APP_NAME'] = 'Premier';
        Functions::clearEnvCache();

        $this->assertSame('Premier', Functions::env('APP_NAME'));

        $_ENV['APP_NAME'] = 'Second';

        $this->assertSame('Premier', Functions::env('APP_NAME'));

        Functions::clearEnvCache();

        $this->assertSame('Second', Functions::env('APP_NAME'));
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

    public function testEnvReturnsTrimmedStringValue(): void
    {
        $_ENV['APP_NAME'] = '  LoliSSR  ';
        Functions::clearEnvCache();

        $this->assertSame('LoliSSR', Functions::env('APP_NAME'));
    }

    public function testEnvConvertsTrueStringToBooleanTrue(): void
    {
        $_ENV['APP_DEBUG'] = 'true';
        Functions::clearEnvCache();

        $this->assertTrue(Functions::env('APP_DEBUG'));
    }

    public function testEnvConvertsFalseStringToBooleanFalse(): void
    {
        $_ENV['APP_DEBUG'] = 'false';
        Functions::clearEnvCache();

        $this->assertFalse(Functions::env('APP_DEBUG'));
    }

    public function testEnvConvertsNullStringToNull(): void
    {
        $_ENV['APP_NAME'] = 'null';
        Functions::clearEnvCache();

        $this->assertNull(Functions::env('APP_NAME', 'fallback'));
    }

    public function testEnvConvertsEmptyStringKeywordToEmptyString(): void
    {
        $_ENV['APP_NAME'] = 'empty';
        Functions::clearEnvCache();

        $this->assertSame('', Functions::env('APP_NAME', 'fallback'));
    }

    public function testBasePathReturnsDefaultSlash(): void
    {
        Functions::clearEnvCache();

        $this->assertSame('/', Functions::basePath());
    }

    public function testBasePathReturnsSlashWhenConfiguredValueIsEmpty(): void
    {
        $_ENV['APP_BASE_PATH'] = '';
        Functions::clearEnvCache();

        $this->assertSame('/', Functions::basePath());
    }

    public function testBasePathReturnsConfiguredPath(): void
    {
        $_ENV['APP_BASE_PATH'] = '/lolissr/';
        Functions::clearEnvCache();

        $this->assertSame('/lolissr/', Functions::basePath());
    }

    public function testBasePathNormalizesPathWithoutLeadingOrTrailingSlashes(): void
    {
        $_ENV['APP_BASE_PATH'] = 'lolissr';
        Functions::clearEnvCache();

        $this->assertSame('/lolissr/', Functions::basePath());
    }

    public function testSiteNameReturnsConfiguredAppName(): void
    {
        $_ENV['APP_NAME'] = 'LoliSSR';
        Functions::clearEnvCache();

        $this->assertSame('LoliSSR', Functions::siteName());
    }

    public function testSiteNameReturnsDefaultValue(): void
    {
        Functions::clearEnvCache();

        $this->assertSame('Site', Functions::siteName());
    }

    public function testPaginationReturnsConfiguredValue(): void
    {
        $_ENV['APP_PAGINATION'] = '12';
        Functions::clearEnvCache();

        $this->assertSame(12, Functions::pagination());
    }

    public function testPaginationReturnsMinimumOne(): void
    {
        $_ENV['APP_PAGINATION'] = '0';
        Functions::clearEnvCache();

        $this->assertSame(1, Functions::pagination());
    }

    public function testAppEnvReturnsConfiguredValue(): void
    {
        $_ENV['APP_ENV'] = 'production';
        Functions::clearEnvCache();

        $this->assertSame('production', Functions::appEnv());
    }

    public function testAppDebugReturnsBooleanTrue(): void
    {
        $_ENV['APP_DEBUG'] = 'true';
        Functions::clearEnvCache();

        $this->assertTrue(Functions::appDebug());
    }

    public function testAppDebugReturnsBooleanFalseByDefault(): void
    {
        Functions::clearEnvCache();

        $this->assertFalse(Functions::appDebug());
    }

    public function testDbHostReturnsConfiguredValue(): void
    {
        $_ENV['DB_HOST'] = '127.0.0.1';
        Functions::clearEnvCache();

        $this->assertSame('127.0.0.1', Functions::dbHost());
    }

    public function testDbNameReturnsConfiguredValue(): void
    {
        $_ENV['DB_NAME'] = 'lolissr';
        Functions::clearEnvCache();

        $this->assertSame('lolissr', Functions::dbName());
    }

    public function testDbUserReturnsConfiguredValue(): void
    {
        $_ENV['DB_USER'] = 'root';
        Functions::clearEnvCache();

        $this->assertSame('root', Functions::dbUser());
    }

    public function testDbPassReturnsConfiguredValue(): void
    {
        $_ENV['DB_PASS'] = 'secret';
        Functions::clearEnvCache();

        $this->assertSame('secret', Functions::dbPass());
    }

    public function testDbCharsetReturnsConfiguredValue(): void
    {
        $_ENV['DB_CHARSET'] = 'utf8';
        Functions::clearEnvCache();

        $this->assertSame('utf8', Functions::dbCharset());
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

    public function testHasUploadedFileReturnsTrueWhenFileIsPresent(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertTrue(Functions::hasUploadedFile('image'));
    }

    public function testHasUploadedFileReturnsFalseWhenFileIsMissing(): void
    {
        $this->assertFalse(Functions::hasUploadedFile('image'));
    }

    public function testHasUploadedFileReturnsFalseWhenNoFileWasUploaded(): void
    {
        $_FILES['image'] = [
            'name' => '',
            'type' => '',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0,
        ];

        $this->assertFalse(Functions::hasUploadedFile('image'));
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

    public function testFileNameReturnsOriginalName(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertSame('cover.jpg', Functions::fileName('image'));
    }

    public function testFileNameReturnsNullWhenFileIsMissing(): void
    {
        $this->assertNull(Functions::fileName('image'));
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

    public function testFileSizeReturnsIntegerSize(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertSame(12345, Functions::fileSize('image'));
    }

    public function testFileSizeReturnsIntegerWhenNumericString(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => '456',
        ];

        $this->assertSame(456, Functions::fileSize('image'));
    }

    public function testFileSizeReturnsNullWhenValueIsInvalid(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 'abc',
        ];

        $this->assertNull(Functions::fileSize('image'));
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

    public function testFileExtensionReturnsNullWhenFileHasNoExtension(): void
    {
        $_FILES['image'] = [
            'name' => 'cover',
            'type' => 'image/jpeg',
            'tmp_name' => 'C:/wamp64/tmp/php123.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertNull(Functions::fileExtension('image'));
    }

    public function testFileMimeTypeReturnsNullWhenTmpFileIsMissing(): void
    {
        $_FILES['image'] = [
            'name' => 'cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => ROOT . '/tests/fake-missing-file.tmp',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->assertNull(Functions::fileMimeType('image'));
    }

    public function testUploadMaxSizeReturnsConfiguredValue(): void
    {
        $_ENV['UPLOAD_MAX_SIZE'] = '1024';
        Functions::clearEnvCache();

        $this->assertSame(1024, Functions::uploadMaxSize());
    }

    public function testUploadMaxSizeReturnsMinimumOne(): void
    {
        $_ENV['UPLOAD_MAX_SIZE'] = '0';
        Functions::clearEnvCache();

        $this->assertSame(1, Functions::uploadMaxSize());
    }

    public function testUploadAllowedExtensionsReturnsDefaultValues(): void
    {
        Functions::clearEnvCache();

        $this->assertSame(['jpg', 'jpeg', 'png', 'webp'], Functions::uploadAllowedExtensions());
    }

    public function testUploadAllowedExtensionsNormalizesAndDeduplicatesValues(): void
    {
        $_ENV['UPLOAD_ALLOWED_EXT'] = ' JPG, png ,jpg,WEBP ,, jpeg ';
        Functions::clearEnvCache();

        $this->assertSame(['jpg', 'png', 'webp', 'jpeg'], Functions::uploadAllowedExtensions());
    }

    public function testUploadAllowedMimeTypesReturnsDefaultValues(): void
    {
        Functions::clearEnvCache();

        $this->assertSame(
            ['image/jpeg', 'image/png', 'image/webp'],
            Functions::uploadAllowedMimeTypes()
        );
    }

    public function testUploadAllowedMimeTypesNormalizesAndDeduplicatesValues(): void
    {
        $_ENV['UPLOAD_ALLOWED_MIME'] = ' IMAGE/JPEG, image/png ,image/jpeg,, IMAGE/WEBP ';
        Functions::clearEnvCache();

        $this->assertSame(
            ['image/jpeg', 'image/png', 'image/webp'],
            Functions::uploadAllowedMimeTypes()
        );
    }

    public function testMangaThumbnailDirectoryReturnsExpectedPath(): void
    {
        $this->assertSame(
            ROOT . '/public/images/mangas/thumbnail/',
            Functions::mangaThumbnailDirectory()
        );
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

    public function testNormalizeSlugRemovesSpecialCharacters(): void
    {
        $this->assertSame(
            'one-piece-édition-1',
            Functions::normalizeSlug('One Piece ! Édition #1')
        );
    }

    public function testNormalizeSlugReturnsEmptyStringWhenValueBecomesEmpty(): void
    {
        $this->assertSame('', Functions::normalizeSlug('@@@###'));
    }

    public function testNormalizeCommentaireReturnsNullWhenValueIsNull(): void
    {
        $this->assertNull(Functions::normalizeCommentaire(null));
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
}