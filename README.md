[![Latest Stable Version](https://poser.pugx.org/neosidekick/qrcode/v/stable)](https://packagist.org/packages/neosidekick/qrcode)
[![License](https://poser.pugx.org/neosidekick/qrcode/license)](LICENSE)

# NEOSidekick.QRCode

## Themed QR code generation for Neos CMS

NEOSidekick.QRCode generates PNG and SVG QR codes for URLs in Neos projects. It is built on
[`chillerlan/php-qrcode`](https://github.com/chillerlan/php-qrcode), supports configurable color themes, and can
optionally place project-specific logos in the center of generated QR codes.

The package exposes small Fusion helpers that create signed Neos URLs to the QR code endpoint and to a ZIP archive
endpoint for bulk downloads. It does not override Neos shortcut rendering.

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

Generate a ZIP archive URL containing multiple themes and formats:

```fusion
qrCodeArchiveUrl = NEOSidekick.QRCode:ArchiveUri {
    uri = 'https://www.example.com/'
    themesCommaSeparated = 'black,grey'
    formatsCommaSeparated = 'png,svg'
    filename = 'example-qrcodes'
}
```

The generated URL points to the archive endpoint:

```text
/neosidekick/qrcode/archive?uri=https%3A%2F%2Fwww.example.com%2F&themesCommaSeparated=black%2Cgrey&formatsCommaSeparated=png%2Csvg&name=example-qrcodes
```

### Shortcut backend override example

The package does not install a shortcut override by default. If a project wants QR code previews directly in the Neos
backend shortcut view, add the override in the site package.

Example Fusion:

```fusion
prototype(Neos.Neos:Shortcut) {
    templatePath = 'resource://Vendor.Site/Private/Templates/FusionObjects/Shortcut.html'

    qrCodeFilenamePrefix = ${String.toLowerCase(String.pregReplace(String.trim(q(documentNode).property('title')), '/[()\[\]\{\}&!. ]+/', '_'))}
    qrCodeFilenamePrefix.@process.trimUnderscoreAtStart = ${String.startsWith(value, '_') ? String.substr(value, 1 - String.length(value)) : value}
    qrCodeFilenamePrefix.@process.trimUnderscoreAtEnd = ${String.endsWith(value, '_') ? String.substr(value, 0, String.length(value) - 1) : value}

    @context.nodeUriInLive = Neos.Neos:NodeUri {
        node = ${q(documentNode).parent().context({workspaceName: 'live'}).get(0)}
        absolute = true
        @process.addUriPathSegment = ${value + q(documentNode).property('uriPathSegment')}
    }

    nodeUriInLiveTooLarge = ${NEOSidekickQRCode.getPayloadBytes(nodeUriInLive) > NEOSidekickQRCode.getMaximumPayloadBytes()}

    qrCodeThemes = ${['black', 'grey']}
    @context.qrCodeThemes = ${this.qrCodeThemes}

    qrCodeLinks = Neos.Fusion:DataStructure {
        png = Neos.Fusion:Map {
            items = ${qrCodeThemes}
            itemName = 'theme'
            keyRenderer = ${theme}
            itemRenderer = NEOSidekick.QRCode:GeneratorUri {
                theme = ${theme}
                uri = ${nodeUriInLive}
                format = 'png'
            }
        }
        svg = Neos.Fusion:Map {
            items = ${qrCodeThemes}
            itemName = 'theme'
            keyRenderer = ${theme}
            itemRenderer = NEOSidekick.QRCode:GeneratorUri {
                theme = ${theme}
                uri = ${nodeUriInLive}
                format = 'svg'
            }
        }
        all = NEOSidekick.QRCode:ArchiveUri {
            themesCommaSeparated = 'black,grey'
            formatsCommaSeparated = 'png,svg'
            uri = ${nodeUriInLive}
            filename = ${q(node).property('uriPathSegment')}
        }
    }
}
```

Example Fluid template excerpt:

```html
<f:if condition="{nodeUriInLiveTooLarge}">
    <f:then>
        <p>The URL is too long and no QR code can be generated.</p>
    </f:then>
    <f:else>
        <div>
            <f:for each="{qrCodeThemes}" as="theme">
                <div>
                    <a href="{qrCodeLinks.svg.{theme}}" target="_blank">
                        <img src="{qrCodeLinks.svg.{theme}}" alt="" />
                    </a>
                    <a download="{qrCodeFilenamePrefix}_{theme}.svg" href="{qrCodeLinks.svg.{theme}}">SVG</a>
                    <a download="{qrCodeFilenamePrefix}_{theme}.png" href="{qrCodeLinks.png.{theme}}">PNG</a>
                </div>
            </f:for>
        </div>
        <a href="{qrCodeLinks.all}" target="_blank" download>Download all QR codes as ZIP</a>
    </f:else>
</f:if>
```

## Configuration

The package intentionally keeps configuration small. Out of the box it ships two themes and QR version 10 with high
error correction:

```yaml
NEOSidekick:
  QRCode:
    version: 10
    eccLevel: 'H'
    moduleShape: 'round'
    archive:
      enabled: true
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

### Module shape

The default `moduleShape: 'round'` renders connected round modules. Projects that need the older square-module output
can set `moduleShape: 'square'`.

### Archive endpoint

The ZIP archive endpoint is enabled by default. It can be disabled if a project should only expose single-code
downloads:

```yaml
NEOSidekick:
  QRCode:
    archive:
      enabled: false
```

When enabled, the endpoint accepts the same `uri` payload as the single QR endpoint, plus comma-separated `themes` and
`formats` arguments. It creates one file per requested theme/format pair and returns an `application/zip` response.

### Capacity

The package validates the byte length of the URI before rendering. The maximum payload is computed from the configured
QR code version and error correction level; it is not a separate setting. With the default `version: 10` and
`eccLevel: 'H'`, the maximum byte length is 119 bytes.

If the payload is too long, the endpoint returns HTTP `400` with a plain-text error message.

The same capacity helpers are available in Fusion/Eel as `NEOSidekickQRCode.getPayloadBytes(payload)` and
`NEOSidekickQRCode.getMaximumPayloadBytes()`.

## Defaults and intentional non-features

- No Neos shortcut override is installed by default.
- The ZIP/download-all endpoint is installed by default and can be disabled through configuration.
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
