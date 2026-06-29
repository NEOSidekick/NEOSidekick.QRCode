<?php

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\Output\QRCodeOutputException;
use finfo;
use Imagick;

class QRImagickWithLogo extends QRImagick
{
    /**
     * @inheritDoc
     * @throws QRCodeOutputException
     */
    public function dump(?string $file = null): string
    {
        // set returnResource to true to skip further processing for now
        $this->options->returnResource = true;

        // there's no need to save the result of dump() into $this->imagick here
        parent::dump($file);

        // set new logo size, leave a border of 1 module (no proportional resize/centering)
        $size = (($this->options->logoSpaceWidth - 2) * $this->options->scale);

        // logo position: the top left corner of the logo space
        $pos = (($this->moduleCount * $this->options->scale - $size) / 2);

        // invoke logo instance
        $imLogo = new Imagick($this->options->pngLogo);
        $imLogo->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 0.85, true);

        // add the logo to the qrcode
        $this->imagick->compositeImage($imLogo, Imagick::COMPOSITE_ADD, $pos, $pos);

        // output (retain functionality of the parent class)
        $imageData = $this->imagick->getImageBlob();

        $this->imagick->destroy();
        $this->saveToFile($imageData, $file);

        if ($this->options->outputBase64) {
            $imageData = $this->toBase64DataURI($imageData, (new finfo(FILEINFO_MIME_TYPE))->buffer($imageData));
        }

        return $imageData;
    }
}
