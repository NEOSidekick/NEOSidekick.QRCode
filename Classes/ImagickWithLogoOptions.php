<?php
// @codingStandardsIgnoreFile

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\QROptions;

class ImagickWithLogoOptions extends QROptions
{
    protected string $pngLogo;

    protected string $moduleColor;

    /**
     * check logo
     *
     * of course, we could accept other formats too.
     * we're not checking for the file type either for simplicity reasons (assuming PNG)
     */
    protected function set_pngLogo(string $pngLogo): void
    {

        if (!file_exists($pngLogo) || !is_file($pngLogo) || !is_readable($pngLogo)) {
            throw new QRCodeException('invalid png logo');
        }

        $this->pngLogo = $pngLogo;
    }

    protected function set_moduleColor(string $color): void
    {
        $this->moduleColor = $color;
    }
}
