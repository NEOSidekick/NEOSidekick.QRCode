<?php

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\Output\QRCodeOutputException;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Output\QRImagick as OriginalQrImagick;
use ImagickPixel;

class QRImagick extends OriginalQrImagick
{
    /**
     * @inheritDoc
     * @throws QRCodeOutputException
     */
    public function dump(?string $file = null)
    {
        foreach ($this->moduleValues as $moduleType => $moduleValue) {
            /** @var ImagickPixel $moduleValue */
            if (QROutputInterface::DEFAULT_MODULE_VALUES[$moduleType] ?? false) {
                $moduleValue->setColor($this->options->moduleColor);
            }
        }

        return parent::dump($file);
    }
}
