<?php
// @codingStandardsIgnoreFile

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\QROptions;

class QROptionsWithModuleColor extends QROptions
{
    protected string $moduleColor;

    protected function set_moduleColor(string $color): void
    {
        $this->moduleColor = $color;
    }
}
