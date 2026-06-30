<?php

namespace NEOSidekick\QRCode\Tests\Unit;

use InvalidArgumentException;
use NEOSidekick\QRCode\QrCodeArchiveRequestValidator;
use PHPUnit\Framework\TestCase;

class QrCodeArchiveRequestValidatorTest extends TestCase
{
    private QrCodeArchiveRequestValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new QrCodeArchiveRequestValidator();
    }

    /**
     * @test
     */
    public function configuredThemesAndSupportedFormatsAreAccepted(): void
    {
        $this->validator->validate($this->createSettings(), ['pink', 'black'], ['png', 'svg']);

        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function duplicateThemesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate QR code themes are not allowed.');

        $this->validator->validate($this->createSettings(), ['pink', 'pink'], ['png']);
    }

    /**
     * @test
     */
    public function duplicateFormatsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate QR code formats are not allowed.');

        $this->validator->validate($this->createSettings(), ['pink'], ['png', 'png']);
    }

    /**
     * @test
     */
    public function unknownThemesAreRejectedBeforeRendering(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only configured QR code themes can be requested.');

        $this->validator->validate($this->createSettings(), ['unknown'], ['png']);
    }

    /**
     * @test
     */
    public function unsupportedFormatsAreRejectedBeforeRendering(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only png and svg formats are supported.');

        $this->validator->validate($this->createSettings(), ['pink'], ['pdf']);
    }

    /**
     * @test
     */
    public function emptyThemeOrFormatListsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one QR code theme and format must be requested.');

        $this->validator->validate($this->createSettings(), [], ['png']);
    }

    /**
     * @return array<string, mixed>
     */
    private function createSettings(): array
    {
        return [
            'themes' => [
                'pink' => [
                    'color' => '#cb1967',
                ],
                'black' => [
                    'color' => '#000',
                ],
            ],
        ];
    }
}
