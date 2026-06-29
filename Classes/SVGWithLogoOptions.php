<?php
// @codingStandardsIgnoreFile

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\QROptions;

class SVGWithLogoOptions extends QROptions
{
    // path to svg logo
    protected string $svgLogo;
    // logo scale in % of QR Code size, clamped to 10%-30%
    protected float $svgLogoScale = 0.20;
    // css class for the logo (defined in $svgDefs)
    protected string $svgLogoCssClass = '';

    // check logo
    protected function set_svgLogo(string $svgLogo): void
    {

        if (!file_exists($svgLogo) || !is_readable($svgLogo)) {
            throw new QRCodeException('invalid svg logo');
        }

        $this->svgLogo = $svgLogo;
    }

    // clamp logo scale
    protected function set_svgLogoScale(float $svgLogoScale): void
    {
        $this->svgLogoScale = max(0.05, min(0.3, $svgLogoScale));
    }
}
