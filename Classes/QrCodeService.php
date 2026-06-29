<?php

namespace NEOSidekick\QRCode;

use chillerlan\QRCode\{Common\EccLevel, Output\QROutputInterface, QRCode};
use chillerlan\QRCode\Data\QRMatrix;
use Neos\Flow\Annotations as Flow;
use InvalidArgumentException;

/**
 * @Flow\Scope("singleton")
 */
class QrCodeService
{
    /**
     * @Flow\InjectConfiguration
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var QrCodeCapacityCalculator
     */
    protected $capacityCalculator;

    /**
     * @param  string  $uri
     * @param  string  $theme
     * @param  string  $format
     * @param  bool  $base64
     * @return string
     */
    public function generateForUri(string $uri, string $theme = 'grey', string $format = 'png', bool $base64 = false): string
    {
        $themeSettings = $this->getThemeSettings($theme);
        $darkColor = $themeSettings['color'];
        $version = $this->getConfiguredVersion();
        $eccLevel = $this->getConfiguredEccLevel();

        $this->capacityCalculator->assertPayloadFits($uri, $version, $eccLevel);

        $commonOptions = [
            'version' => $version,
            'eccLevel' => $eccLevel,
            'outputBase64' => $base64,
            'addQuietzone' => false,
            'drawLightModules' => false,
            'drawCircularModules' => true,
            'circleRadius' => 0.45,
            'connectPaths' => true,
            'keepAsSquare' => [
                QRMatrix::M_FINDER_DARK,
                QRMatrix::M_FINDER_DOT,
                QRMatrix::M_ALIGNMENT_DARK,
            ]
        ];

        $options = match ($format) {
            'png' => $this->createPngOptions($commonOptions, $themeSettings, $darkColor),
            'svg' => $this->createSvgOptions($commonOptions, $themeSettings, $darkColor),
            default => throw new InvalidArgumentException('Only png and svg formats are supported', 1689167094322),
        };

        $qrOutputInterface = new QRCode($options);

        return $qrOutputInterface->render($uri);
    }

    public function getMaximumPayloadBytes(): int
    {
        return $this->capacityCalculator->getMaximumPayloadBytes(
            $this->getConfiguredVersion(),
            $this->getConfiguredEccLevel()
        );
    }

    public function getPayloadBytes(string $payload): int
    {
        return $this->capacityCalculator->getPayloadBytes($payload);
    }

    protected function createPngOptions(array $commonOptions, array $themeSettings, string $darkColor): \chillerlan\QRCode\QROptions
    {
        $pngLogo = $themeSettings['logo']['png'] ?? null;
        $options = [
            ...$commonOptions,
            'outputType' => QROutputInterface::CUSTOM,
            'outputInterface' => QRImagick::class,
            'imageTransparent' => true,
            'scale' => 20,
            'bgColor' => 'transparent',
            'moduleColor' => $darkColor,
        ];

        if ($pngLogo === null) {
            return new QROptionsWithModuleColor($options);
        }

        return new ImagickWithLogoOptions([
            ...$options,
            'addLogoSpace' => true,
            'pngLogo' => $pngLogo,
            'outputInterface' => QRImagickWithLogo::class,
            'logoSpaceWidth' => 15,
            'logoSpaceHeight' => 15,
        ]);
    }

    protected function createSvgOptions(array $commonOptions, array $themeSettings, string $darkColor): \chillerlan\QRCode\QROptions
    {
        $svgDefs = "
                    <style><![CDATA[
                        .dark, .dark * {fill: $darkColor;}
                        .light {fill: transparent;}
                    ]]></style>";
        $svgLogo = $themeSettings['logo']['svg'] ?? null;

        if ($svgLogo === null) {
            return new \chillerlan\QRCode\QROptions([
                ...$commonOptions,
                'svgDefs' => $svgDefs,
                'outputType' => QROutputInterface::MARKUP_SVG
            ]);
        }

        return new SVGWithLogoOptions([
            ...$commonOptions,
            'svgDefs' => $svgDefs,
            'outputType' => QROutputInterface::CUSTOM,
            'outputInterface' => QRSvgWithLogo::class,
            'svgLogo' => $svgLogo,
            'svgLogoScale' => 0.22,
            'svgLogoCssClass' => 'dark',
        ]);
    }

    protected function getThemeSettings(string $theme): array
    {
        if (!array_key_exists($theme, $this->settings['themes'] ?? [])) {
            throw new InvalidArgumentException('The requested QR code theme is not configured', 1689241163926);
        }

        $themeSettings = $this->settings['themes'][$theme];

        if (!isset($themeSettings['color'])) {
            throw new InvalidArgumentException('The requested QR code theme does not define a color', 1689241163927);
        }

        return $themeSettings;
    }

    protected function getConfiguredVersion(): int
    {
        return (int)($this->settings['version'] ?? 10);
    }

    protected function getConfiguredEccLevel(): int
    {
        $eccLevel = strtoupper((string)($this->settings['eccLevel'] ?? 'H'));

        return match ($eccLevel) {
            'L' => EccLevel::L,
            'M' => EccLevel::M,
            'Q' => EccLevel::Q,
            'H' => EccLevel::H,
            default => throw new InvalidArgumentException('The configured QR code ECC level is invalid', 1689167094324),
        };
    }
}
