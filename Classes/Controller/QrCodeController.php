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
     * @return void
     */
    public function indexAction(string $uri, string $theme = 'black', string $format = 'png'): void
    {
        $contentType = match ($format) {
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            default => null,
        };

        if ($contentType === null) {
            $this->respondWithError(400, 'The requested QR code format is not supported');
            return;
        }

        try {
            $this->response->setContentType($contentType);
            $this->response->setContent($this->qrCodeService->generateForUri($uri, $theme, $format));
        } catch (InvalidArgumentException $exception) {
            $this->respondWithError(400, $exception->getMessage());
        } catch (Throwable $exception) {
            $this->respondWithError(500, 'The QR code could not be generated');
        }
    }

    protected function respondWithError(int $statusCode, string $message): void
    {
        $this->response->setStatusCode($statusCode);
        $this->response->setContentType('text/plain');
        $this->response->setContent($message);
    }
}
