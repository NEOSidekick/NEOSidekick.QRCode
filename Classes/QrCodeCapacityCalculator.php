<?php

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use NEOSidekick\QRCode\Exception\PayloadTooLongException;

class QrCodeCapacityCalculator
{
    public function getMaximumPayloadBytes(int $version, int $eccLevel): int
    {
        $maximumBits = (new EccLevel($eccLevel))->getMaxBitsForVersion(new Version($version));
        $lengthIndicatorBits = $version <= 9 ? 8 : 16;

        return (int)floor(($maximumBits - 4 - $lengthIndicatorBits) / 8);
    }

    public function getPayloadBytes(string $payload): int
    {
        return strlen($payload);
    }

    public function assertPayloadFits(string $payload, int $version, int $eccLevel): void
    {
        $payloadBytes = $this->getPayloadBytes($payload);
        $maximumPayloadBytes = $this->getMaximumPayloadBytes($version, $eccLevel);

        if ($payloadBytes > $maximumPayloadBytes) {
            throw new PayloadTooLongException($payloadBytes, $maximumPayloadBytes);
        }
    }
}
