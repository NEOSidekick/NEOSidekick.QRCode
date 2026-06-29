<?php

namespace NEOSidekick\QRCode\Tests\Unit;

use chillerlan\QRCode\Common\EccLevel;
use NEOSidekick\QRCode\Exception\PayloadTooLongException;
use NEOSidekick\QRCode\QrCodeCapacityCalculator;
use PHPUnit\Framework\TestCase;

class QrCodeCapacityCalculatorTest extends TestCase
{
    private QrCodeCapacityCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new QrCodeCapacityCalculator();
    }

    /**
     * @test
     */
    public function versionTenWithHighErrorCorrectionMatchesTheLegacyKapschLimit(): void
    {
        self::assertSame(119, $this->calculator->getMaximumPayloadBytes(10, EccLevel::H));
    }

    /**
     * @test
     */
    public function payloadLengthUsesUtf8Bytes(): void
    {
        self::assertSame(4, $this->calculator->getPayloadBytes('äö'));
    }

    /**
     * @test
     */
    public function payloadAtCapacityIsAccepted(): void
    {
        $this->calculator->assertPayloadFits(str_repeat('a', 119), 10, EccLevel::H);

        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function payloadAboveCapacityIsRejected(): void
    {
        $this->expectException(PayloadTooLongException::class);
        $this->expectExceptionMessage('maximum 119 bytes');

        $this->calculator->assertPayloadFits(str_repeat('a', 120), 10, EccLevel::H);
    }
}
