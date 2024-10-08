
# Laravel Link Preview

**Link preview generation for Laravel applications.** This package generates link previews for Laravel applications, similar to how social media platforms display link previews when a URL is shared.

## Installation

You can install the package via Composer by running the following command:

```bash
composer require cardei/link-preview
```

After installation, if you want to publish the configuration file to customize options such as enabling logs, you can run:

```bash
php artisan vendor:publish --tag=link-preview-config
```

This command will create a configuration file at `config/link-preview.php`.

### Requirements

- PHP >= 8.2
- Laravel 10.x or 11.x
- Additional dependencies:
  - `guzzlehttp/guzzle`: ^7.4
  - `symfony/dom-crawler`: ^7.0
  - `symfony/css-selector`: ^7.0

## Usage

### Generating Link Previews

The package automatically detects URLs in different formats and generates link previews using the metadata from the target URL (e.g., titles, images, descriptions). It supports various platforms, including YouTube, Vimeo, and general HTML pages.

To use the package, you can call the `link-preview` service in your Laravel controllers or services:

```php
$linkPreview = app('link-preview');
$preview = $linkPreview->setUrl('https://example.com')->getPreview();
```

You can access the `title`, `description`, `cover image`, and more from the `$preview` object.

### Supported Platforms

- **YouTube**: Automatically detects and generates an embedded player for YouTube links.
- **Vimeo**: Similar to YouTube, this generates an embedded player for Vimeo links.
- **General HTML Links**: Extracts metadata such as titles, descriptions, and images from general websites.

## Configuration

The package provides a configuration file that can be published using the `vendor:publish` command. By default, the package includes basic configuration for logging and URL processing:

```php
return [
    'enable_logging' => env('LINK_PREVIEW_ENABLE_LOGS', false),
];
```

- **`enable_logging`**: If set to `true`, the package will log information about the URLs being processed when `APP_DEBUG=true`.

To enable logging, you can add the following to your `.env` file:

```
APP_DEBUG=true
LINK_PREVIEW_ENABLE_LOGS=true
```

## Versioning and Branches

- **v1.0.0 - 1.0.1**: Supports Laravel 10.x
- **v2.x**: Supports Laravel 11.x and newer versions.

- 2.1.0-DEV - DO NOT USE IN PRODUCTION

Branches:
- `dev-main`: Development branch with the latest updates.
- `2.1.0-DEV`: Latest development version for Laravel 11.x support.

## Contributing

Feel free to fork this repository and submit pull requests. Contributions are welcome to improve the package or extend its functionality.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

