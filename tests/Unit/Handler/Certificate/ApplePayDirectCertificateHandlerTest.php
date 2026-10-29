<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Handler\Certificate;

use Mollie;
use Mollie\Handler\Certificate\ApplePayDirectCertificateHandler;
use Mollie\Handler\Certificate\Exception\ApplePayDirectCertificateCreation;
use PHPUnit\Framework\TestCase;

class ApplePayDirectCertificateHandlerTest extends TestCase
{
    const MOLLIE_PSP_ID = 'D9C7F701C8C6F2C6F3D656C09944E32200B176F152E58D9140C1C53AA8246E60';

    /** @var string */
    private $documentRoot;

    /** @var string|null */
    private $originalDocumentRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDocumentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
        $this->documentRoot = sys_get_temp_dir() . '/mollie-apple-pay-' . uniqid('', true);
        mkdir($this->documentRoot);
        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot;
    }

    protected function tearDown(): void
    {
        $file = $this->getShopFilePath();

        if (file_exists($file)) {
            unlink($file);
        }

        if (is_dir(dirname($file))) {
            rmdir(dirname($file));
        }

        rmdir($this->documentRoot);
        $_SERVER['DOCUMENT_ROOT'] = $this->originalDocumentRoot;

        if ($this->originalDocumentRoot === null) {
            unset($_SERVER['DOCUMENT_ROOT']);
        }

        parent::tearDown();
    }

    public function testHandleCopiesBundledFileWhenNoneExists(): void
    {
        $this->createHandler()->handle();

        $this->assertFileEquals(ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_FILE_LOCATION, $this->getShopFilePath());
    }

    public function testHandleAcceptsPreviousMollieFile(): void
    {
        $this->writeShopFile($this->getPreviousMollieFile());

        $handler = $this->createHandler();

        $this->assertFalse($handler->hasConflict());

        $handler->handle();

        $this->assertStringEqualsFile($this->getShopFilePath(), $this->getPreviousMollieFile());
    }

    /**
     * @dataProvider foreignFileProvider
     */
    public function testHandleRejectsFileOfAnotherProvider(string $contents): void
    {
        $this->writeShopFile($contents);

        $handler = $this->createHandler();

        $this->assertTrue($handler->hasConflict());

        try {
            $handler->handle();
            $this->fail('Expected a file conflict exception');
        } catch (ApplePayDirectCertificateCreation $exception) {
            $this->assertSame(ApplePayDirectCertificateCreation::FILE_CONFLICT_EXCEPTION, $exception->getCode());
        }

        $this->assertStringEqualsFile($this->getShopFilePath(), $contents);
    }

    public function testRefreshReplacesPreviousMollieFile(): void
    {
        $this->writeShopFile($this->getPreviousMollieFile());

        $this->createHandler()->refresh();

        $this->assertFileEquals(ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_FILE_LOCATION, $this->getShopFilePath());
    }

    public function testRefreshKeepsCurrentMollieFile(): void
    {
        $this->writeShopFile((string) file_get_contents(ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_FILE_LOCATION));

        $this->createHandler()->refresh();

        $this->assertFileEquals(ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_FILE_LOCATION, $this->getShopFilePath());
    }

    public function testRefreshDoesNotCreateMissingFile(): void
    {
        $this->createHandler()->refresh();

        $this->assertFalse(file_exists($this->getShopFilePath()));
    }

    /**
     * @dataProvider foreignFileProvider
     */
    public function testRefreshLeavesFileOfAnotherProviderUntouched(string $contents): void
    {
        $this->writeShopFile($contents);

        $this->createHandler()->refresh();

        $this->assertStringEqualsFile($this->getShopFilePath(), $contents);
    }

    public function testRefreshThrowsAndKeepsPreviousFileWhenFolderIsNotWritable(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('root can write to read-only folders');
        }

        $this->writeShopFile($this->getPreviousMollieFile());
        chmod(dirname($this->getShopFilePath()), 0555);

        try {
            $this->createHandler()->refresh();
            $this->fail('Expected a file copy exception');
        } catch (ApplePayDirectCertificateCreation $exception) {
            $this->assertSame(ApplePayDirectCertificateCreation::FILE_COPY_EXCEPTON, $exception->getCode());
        } finally {
            chmod(dirname($this->getShopFilePath()), 0755);
        }

        $this->assertStringEqualsFile($this->getShopFilePath(), $this->getPreviousMollieFile());
        $this->assertSame(['apple-developer-merchantid-domain-association'], array_values(array_diff(scandir(dirname($this->getShopFilePath())), ['.', '..'])));
    }

    public function testRefreshReplacesReadOnlyPreviousMollieFile(): void
    {
        $this->writeShopFile($this->getPreviousMollieFile());
        chmod($this->getShopFilePath(), 0444);

        $this->createHandler()->refresh();

        $this->assertFileEquals(ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_FILE_LOCATION, $this->getShopFilePath());
    }

    public function foreignFileProvider(): array
    {
        return [
            'other psp id' => [bin2hex(json_encode(['pspId' => 'ABCDEF0123456789', 'version' => 1]))],
            'not hex encoded' => ['{"pspId":"' . self::MOLLIE_PSP_ID . '"}'],
            'empty file' => [''],
        ];
    }

    public function testNoConflictWhenNoFileExists(): void
    {
        $this->assertFalse($this->createHandler()->hasConflict());
    }

    private function getPreviousMollieFile(): string
    {
        return strtoupper(bin2hex(json_encode([
            'pspId' => self::MOLLIE_PSP_ID,
            'version' => 1,
            'createdOn' => 1715203977496,
            'signature' => '308006092a864886f70d010702a080',
        ])));
    }

    private function createHandler(): ApplePayDirectCertificateHandler
    {
        $module = $this->createMock(Mollie::class);
        $module->method('l')->willReturnArgument(0);

        return new ApplePayDirectCertificateHandler($module);
    }

    private function writeShopFile(string $contents): void
    {
        mkdir(dirname($this->getShopFilePath()));
        file_put_contents($this->getShopFilePath(), $contents);
    }

    private function getShopFilePath(): string
    {
        return $this->documentRoot
            . ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_FOLDER
            . ApplePayDirectCertificateHandler::APPLE_PAY_CERTIFICATE_PS_FILE;
    }
}
