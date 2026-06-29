[![Latest Stable Version](https://poser.pugx.org/neosidekick/qrcode/v/stable)](https://packagist.org/packages/neosidekick/qrcode)
[![License](https://poser.pugx.org/neosidekick/qrcode/license)](LICENSE)

# NEOSidekick.QRCode

## Themed QR code generation for Neos CMS

NEOSidekick.QRCode generates PNG and SVG QR codes for URLs in Neos projects. It is built on
[`chillerlan/php-qrcode`](https://github.com/chillerlan/php-qrcode), supports configurable color themes, and can
optionally place project-specific logos in the center of generated QR codes.

The package exposes a small Fusion helper that creates signed Neos URLs to the QR code endpoint. It does not override
Neos shortcut rendering and it does not expose a ZIP/download-all endpoint by default.

## Installation

NEOSidekick.QRCode is available via Packagist:

```bash
composer require neosidekick/qrcode
```

We use semantic versioning, so every breaking change will increase the major version number.

## Usage

Generate a QR code URL from Fusion:

```fusion
qrCodeUrl = NEOSidekick.QRCode:GeneratorUri {
    uri = 'https://www.example.com/'
    theme = 'black'
    format = 'svg'
}
```

The generated URL points to the package endpoint:

```text
/neosidekick/qrcode?uri=https%3A%2F%2Fwww.example.com%2F&theme=black&format=svg
```

Supported formats are `png` and `svg`.

## Configuration

The package intentionally keeps configuration small. Out of the box it ships two themes and QR version 10 with high
error correction:

```yaml
NEOSidekick:
  QRCode:
    version: 10
    eccLevel: 'H'
    themes:
      grey:
        color: 'rgb(237, 237, 237)'
      black:
        color: '#000'
```

### Themes

Each theme needs a `color`. The color is used for the dark QR code modules in both PNG and SVG output.

```yaml
NEOSidekick:
  QRCode:
    themes:
      brand:
        color: '#cb1967'
```

### Logos

Logos are disabled by default. A theme only renders a logo when the logo is explicitly configured for that theme.

```yaml
NEOSidekick:
  QRCode:
    themes:
      brand:
        color: '#cb1967'
        logo:
          png: 'resource://Vendor.Site/Private/QrCode/logo_brand.png'
          svg: 'resource://Vendor.Site/Private/QrCode/logo.svg'
```

Configure PNG and SVG separately. If only `logo.png` is configured, PNG output contains a logo and SVG output stays
plain. If only `logo.svg` is configured, SVG output contains a logo and PNG output stays plain.

### Capacity

The package validates the byte length of the URI before rendering. The maximum payload is computed from the configured
QR code version and error correction level; it is not a separate setting. With the default `version: 10` and
`eccLevel: 'H'`, the maximum byte length is 119 bytes.

If the payload is too long, the endpoint returns HTTP `400` with a plain-text error message.

## Defaults and intentional non-features

- No Neos shortcut override is installed by default.
- No ZIP/download-all endpoint is installed by default.
- No logo is rendered unless it is configured on the selected theme.
- The package targets `chillerlan/php-qrcode` v5 and does not include v4 compatibility code.

## Development

Run the test suite from a Neos distribution with the package installed in `DistributionPackages/NEOSidekick.QRCode`:

```bash
composer test:style
composer test:stan
composer test:unit
```

GitHub Actions runs style checks, PHPStan and PHPUnit against Neos 8.3 and 8.4.

## License

The GNU General Public License, please see [License File](LICENSE) for more information.
