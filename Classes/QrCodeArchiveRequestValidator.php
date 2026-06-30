<?php

namespace NEOSidekick\QRCode;

use InvalidArgumentException;

class QrCodeArchiveRequestValidator
{
    /**
     * @var array<string>
     */
    private const SUPPORTED_FORMATS = ['png', 'svg'];

    /**
     * @param array<string, mixed> $settings
     * @param array<string> $themes
     * @param array<string> $formats
     */
    public function validate(array $settings, array $themes, array $formats): void
    {
        if ($themes === [] || $formats === []) {
            throw new InvalidArgumentException('At least one QR code theme and format must be requested.');
        }

        $this->assertNoDuplicateValues($themes, 'themes');
        $this->assertNoDuplicateValues($formats, 'formats');

        $configuredThemes = array_keys($settings['themes'] ?? []);
        if ($configuredThemes === []) {
            throw new InvalidArgumentException('No QR code themes are configured.');
        }

        $unknownThemes = array_values(array_diff($themes, $configuredThemes));
        if ($unknownThemes !== []) {
            throw new InvalidArgumentException('Only configured QR code themes can be requested.');
        }

        $unsupportedFormats = array_values(array_diff($formats, self::SUPPORTED_FORMATS));
        if ($unsupportedFormats !== []) {
            throw new InvalidArgumentException('Only png and svg formats are supported.');
        }

        $maximumFileCount = count($configuredThemes) * count(self::SUPPORTED_FORMATS);
        if (count($themes) * count($formats) > $maximumFileCount) {
            throw new InvalidArgumentException('The requested QR code archive contains too many files.');
        }
    }

    /**
     * @param array<string> $values
     */
    private function assertNoDuplicateValues(array $values, string $label): void
    {
        if (count($values) !== count(array_unique($values))) {
            throw new InvalidArgumentException(sprintf('Duplicate QR code %s are not allowed.', $label));
        }
    }
}
