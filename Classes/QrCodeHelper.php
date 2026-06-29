<?php

namespace NEOSidekick\QRCode;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

class QrCodeHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var QrCodeService
     */
    protected $qrCodeService;

    public function generateForUri(string $uri, string $theme = 'grey', string $format = 'png', bool $base64 = false): string
    {
        return $this->qrCodeService->generateForUri($uri, $theme, $format, $base64);
    }

    public function getMaximumPayloadBytes(): int
    {
        return $this->qrCodeService->getMaximumPayloadBytes();
    }

    public function getPayloadBytes(string $string): int
    {
        return $this->qrCodeService->getPayloadBytes($string);
    }


    /**
     * @inheritDoc
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
