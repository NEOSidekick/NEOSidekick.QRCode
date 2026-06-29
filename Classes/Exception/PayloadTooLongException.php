<?php

namespace NEOSidekick\QRCode\Exception;

use InvalidArgumentException;

class PayloadTooLongException extends InvalidArgumentException
{
    public function __construct(
        private readonly int $payloadBytes,
        private readonly int $maximumPayloadBytes
    ) {
        parent::__construct(
            sprintf(
                'The URI is too long for the configured QR code capacity (%d bytes, maximum %d bytes)',
                $payloadBytes,
                $maximumPayloadBytes
            ),
            1689167094323
        );
    }

    public function getPayloadBytes(): int
    {
        return $this->payloadBytes;
    }

    public function getMaximumPayloadBytes(): int
    {
        return $this->maximumPayloadBytes;
    }
}
