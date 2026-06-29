<?php

namespace NEOSidekick\QRCode\Controller;

/*
 * This file is part of the NEOSidekick.QRCode package.
 */

use NEOSidekick\QRCode\QrCodeService;
use InvalidArgumentException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Throwable;

class QrCodeController extends ActionController
{
    /**
     * @Flow\Inject
     * @var QrCodeService
     */
    protected $qrCodeService;

    /**
     * @param  string  $uri
     * @param  string  $theme
     * @return string
     */
    public function indexAction(string $uri, string $theme = 'black', string $format = 'png'): string
    {
        $contentType = match ($format) {
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            default => null,
        };

        if ($contentType === null) {
            return $this->respondWithError(400, 'The requested QR code format is not supported');
        }

        try {
            $this->response->setContentType($contentType);
            return $this->qrCodeService->generateForUri($uri, $theme, $format);
        } catch (InvalidArgumentException $exception) {
            return $this->respondWithError(400, $exception->getMessage());
        } catch (Throwable) {
            return $this->respondWithError(500, 'The QR code could not be generated');
        }
    }

    protected function respondWithError(int $statusCode, string $message): string
    {
        $this->response->setStatusCode($statusCode);
        $this->response->setContentType('text/plain');
        return $message;
    }
}
