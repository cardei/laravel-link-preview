# Laravel Link 🔗 Preview (βETA) 🚧 WIP - Work In Progress

**Link preview generation 🛠️ for Laravel 🚀 applications.** This package 📦 generates link previews for Laravel applications, similar to how social media platforms display link previews when a URL is shared.

## Features 🌟
- **Automatic Metadata Extraction** 📝: Extracts titles 🏷️, descriptions 📄, and images 🖼️ from URLs to generate rich link previews.
- **Supports Popular Platforms** 🌐: Built-in support for platforms like YouTube ▶️ and Vimeo 🎥 for embedded previews.
- **Customizable Configuration** ⚙️: Fine-tune 🔧 the package to meet your application's specific requirements.
- **Laravel Integration** 🤝: Seamlessly integrates into your Laravel projects with service providers and facades.
- **Simple Usage** 😊: Easy to implement in any controller 🎛️ or service.

## Compatibility ✅
- **PHP Version**: PHP >= 8.2 🐘
- **Laravel Versions**: Laravel 10.x and 11.x 🚀

### Dependencies 📦
- **Guzzle** (`guzzlehttp/guzzle`): ^7.4 - For HTTP 🌐 requests to fetch URL metadata.
- **Symfony Dom Crawler** (`symfony/dom-crawler`): ^7.0 - For parsing HTML content 📄.
- **Symfony CSS Selector** (`symfony/css-selector`): ^7.0 - For extracting metadata from the parsed HTML.

## Installation 🛠️
To install the package via Composer, run the following command:

```bash
composer require cardei/link-preview
```

After installation, you can publish the configuration file if you need to customize the settings:

```bash
php artisan vendor:publish --tag=link-preview-config
```

This will create a configuration file at `config/link-preview.php`.

### Requirements 📋
- **PHP >= 8.2** 🐘
- **Laravel 10.x or 11.x** 🚀

### Configuration ⚙️
The package provides a configuration file that can be published using the `vendor:publish` command. By default, the package includes basic configuration for logging 📝 and URL processing:

```php
return [
    'enable_logging' => env('LINK_PREVIEW_ENABLE_LOGS', false),
];
```

- **`enable_logging`**: If set to `true` ✅, the package will log information about the URLs being processed when `APP_DEBUG=true`.

To enable logging, you can add the following to your `.env` file:

```
APP_DEBUG=true
LINK_PREVIEW_ENABLE_LOGS=true
```

## Usage 💻

### Generating Link Previews 🔗
The package automatically detects URLs and generates previews using metadata from the target URL (e.g., titles 🏷️, images 🖼️, descriptions 📄). It supports platforms such as YouTube ▶️, Vimeo 🎥, and general HTML pages 📄.

To use the package, simply call the `link-preview` service in your Laravel controllers or services:

```php
$linkPreview = app('link-preview');
$preview = $linkPreview->setUrl('https://example.com')->getPreview();
```

You can then access metadata like `title` 🏷️, `description` 📄, and `cover image` 🖼️ from the `$preview` object.

### Supported Platforms 🌐
- **YouTube** ▶️: Automatically generates embedded previews for YouTube links. **Note**: Integration with the YouTube API is required for optimal performance. This is particularly important when running the package from cloud instances, as direct page scraping for YouTube links might fail due to network restrictions or bot detection mechanisms. Using the YouTube API ensures consistent and reliable previews.
- **Vimeo** 🎥: Similar to YouTube, generates embedded previews for Vimeo links.
- **General HTML Links** 🌍: Extracts metadata such as titles 🏷️, descriptions 📄, and images 🖼️ from any general webpage.

## Why Use Laravel Link Preview? 🤔
- **Simplicity** 😊: This package is designed to be easy to integrate and use within any Laravel project, with minimal configuration required.
- **Rich Integration** 💎: Direct integration with Laravel services and easy access via facades.
- **Customizable** ⚙️: Configuration options allow you to tailor the package's behavior to your specific needs, including the ability to add new parsers.
- **Actively Developed** 🛠️: Ongoing support and compatibility with the latest versions of Laravel.

## Integration 🤝
The package provides seamless integration into your Laravel projects by including:

- **Service Providers** 🛠️: The `LaravelServiceProvider` is automatically registered.
- **Facades** 🏷️: Use the provided facade to easily access the `link-preview` functionality.

### Example: Using in a Controller 📄
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cardei\LinkPreview\Facades\LinkPreview;

class PreviewController extends Controller
{
    public function showPreview(Request $request)
    {
        $url = $request->input('url');
        $preview = LinkPreview::setUrl($url)->getPreview();
        
        return view('preview', ['preview' => $preview]);
    }
}
```

## TODO 📝
- **Support More Platforms** 🌐: Expand support for additional platforms, such as SoundCloud 🎶 and TikTok 🎵.
- **Advanced Error Handling** ⚠️: Improve error handling for failed previews.
- **Caching** 🗄️: Implement caching for previews to avoid multiple requests for the same URL.
- **Internationalization** 🌍: Add support for parsing metadata in different languages.

## Versioning and Branches 📌
- **v1.0.0 - 1.0.1**: Supports Laravel 10.x 🚀
- **v2.x**: Supports Laravel 11.x and newer versions 🚀

Branches:
- `main` 🚀: Default branch with the latest stable version ready for production.
- `dev-main` 🛠️: Development branch with the latest updates.
- `2.1.0-DEV` 🚧: Latest development version for Laravel 11.x support.

## Documentation 📚
For detailed documentation, refer to the [GitHub Wiki](https://github.com/cardei/laravel-link-preview/wiki).

## Contributing 🤝
Contributions are always welcome! If you'd like to contribute, please fork this repository, make your changes, and submit a pull request 📥.

## License 📜
This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support 🆘
For issues and feature requests, please visit the [GitHub Issues](https://github.com/cardei/laravel-link-preview/issues) page.

If you have any questions or need support, you can contact [M. Catalin Cardei](mailto:mc@cardei.studio).

## Why Laravel Link Preview is Better? 🏆
- **Native Laravel Integration** 🤝: Unlike other link preview libraries that require extensive setup, this package is designed with Laravel in mind, providing native support for service providers and facades.
- **Modular Parser System** 🔧: The parser system allows for easy addition of custom parsers, making it extensible.
- **Lightweight and Fast** ⚡: Optimized for performance, it does not add unnecessary overhead to your application.
- **Open Source and Transparent** 🔍: The code is available on GitHub, providing transparency and the ability to modify the package to fit your needs.
