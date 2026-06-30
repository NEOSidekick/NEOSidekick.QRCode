<?php

namespace NEOSidekick\QRCode\Controller;

/*
 * This file is part of the NEOSidekick.QRCode package.
 */

use InvalidArgumentException;
use NEOSidekick\QRCode\QrCodeArchiveRequestValidator;
use NEOSidekick\QRCode\QrCodeService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Utility\Environment;
use RuntimeException;
use Throwable;
use ZipArchive;

class QrCodeArchiveController extends ActionController
{
    /**
     * @Flow\Inject
     * @var QrCodeService
     */
    protected $qrCodeService;

    /**
     * @Flow\Inject
     * @var QrCodeArchiveRequestValidator
     */
    protected $requestValidator;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected $settings = [];

    public function downloadAllAction(
        string $uri,
        string $themesCommaSeparated,
        string $formatsCommaSeparated,
        string $name = 'qrcodes'
    ): string {
        if (($this->settings['archive']['enabled'] ?? false) !== true) {
            return $this->respondWithPlainText('The QR code archive endpoint is disabled.', 404);
        }

        if (!class_exists(ZipArchive::class)) {
            return $this->respondWithPlainText('The PHP ZIP extension is not available.', 500);
        }

        $themes = $this->splitCommaSeparatedValue($themesCommaSeparated);
        $formats = $this->splitCommaSeparatedValue($formatsCommaSeparated);

        try {
            $this->requestValidator->validate($this->settings, $themes, $formats);
        } catch (InvalidArgumentException $exception) {
            return $this->respondWithPlainText($exception->getMessage(), 400);
        }

        $zip = new ZipArchive();
        $zipFilename = tempnam($this->environment->getPathToTemporaryDirectory(), 'qrcode-archive-');
        if (!is_string($zipFilename)) {
            return $this->respondWithPlainText('QR code archive could not be created.', 500);
        }

        $zipIsOpen = false;
        try {
            if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('QR code archive could not be opened.');
            }
            $zipIsOpen = true;

            $downloadName = $this->sanitizeDownloadName($name);
            foreach ($themes as $theme) {
                foreach ($formats as $format) {
                    $filename = sprintf('%s-%s.%s', $downloadName, $theme, $format);
                    $content = $this->qrCodeService->generateForUri($uri, $theme, $format);
                    if (!$zip->addFromString($filename, $content)) {
                        throw new RuntimeException('QR code file could not be added to the archive.');
                    }
                }
            }

            if (!$zip->close()) {
                throw new RuntimeException('QR code archive could not be finalized.');
            }
            $zipIsOpen = false;

            $archiveContent = file_get_contents($zipFilename);
            if (!is_string($archiveContent)) {
                throw new RuntimeException('QR code archive could not be read.');
            }
        } catch (InvalidArgumentException $exception) {
            if ($zipIsOpen) {
                $zip->close();
            }
            return $this->respondWithPlainText($exception->getMessage(), 400);
        } catch (Throwable) {
            if ($zipIsOpen) {
                $zip->close();
            }
            return $this->respondWithPlainText('QR code archive could not be generated.', 500);
        } finally {
            if (file_exists($zipFilename)) {
                unlink($zipFilename);
            }
        }

        $downloadName = $this->sanitizeDownloadName($name);
        $this->response->setContentType('application/zip');
        $this->response->setHttpHeader('Content-Disposition', sprintf('attachment; filename="%s.zip"', $downloadName));
        $this->response->setHttpHeader('Content-Length', (string)strlen($archiveContent));
        $this->response->setHttpHeader('Pragma', 'no-cache');
        $this->response->setHttpHeader('Expires', '0');

        return $archiveContent;
    }

    /**
     * @return array<string>
     */
    private function splitCommaSeparatedValue(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function sanitizeDownloadName(string $name): string
    {
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $name);
        $sanitizedName = trim((string)$sanitizedName, '-_.');

        return $sanitizedName !== '' ? $sanitizedName : 'qrcodes';
    }

    private function respondWithPlainText(string $message, int $statusCode): string
    {
        $this->response->setStatusCode($statusCode);
        $this->response->setContentType('text/plain');

        return $message;
    }
}
