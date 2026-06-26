# WPZylos Views

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-KYNetCode-181717?logo=github)](https://github.com/KYNetCode/wpzylos-views)

PHP template rendering with optional Twig adapter for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/KYNetCode/wpzylos-views/issues)**

---

## Features

- **PHP Templates** - Native PHP templates with output buffering and safe data extraction
- **Optional Twig Support** - Plug in Twig for autoescape and template syntax
- **Multi-Engine Architecture** - Register custom template engines via `EngineInterface`
- **Shared View Data** - Share data globally across all views
- **Deferred Rendering** - Create `View` instances and render later
- **Dot Notation** - Reference views with dot notation (`admin.settings`)
- **CssHelper** - DaisyUI CSS prefix helper for style isolation (`cls()`, `prefix()`, `adminRootId()`, `adminOpen()`, `adminClose()`)
- **JsMount** - Vue.js/React mount point renderer (`adminMount()`, `frontendMount()`, `shortcodeMount()`)
- **Vue.js 3 Options API** - First-class support for Vue.js 3 with Options API
- **React 19** - Opt-in React 19 support for modern frontend development
- **DaisyUI v5 + Tailwind v4** - Integrated DaisyUI component styling with CSS prefix isolation

---

## Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## Installation

```bash
composer require KYNetCode/wpzylos-views
```

For Twig template support (optional):

```bash
composer require twig/twig:^3.0
```

---

## Quick Start

```php
use WPZylos\Framework\Views\ViewFactory;

// Get from container
$views = $app->make('view');

// Render a view immediately
echo $views->render('admin.settings', [
    'title' => 'Settings',
    'options' => $options,
]);

// Or create a deferred View instance
$view = $views->make('admin.dashboard')
    ->with('stats', $stats)
    ->with('user', $currentUser);

echo $view; // Renders when cast to string
```

---

## Core Features

### Rendering Views

The `ViewFactory` resolves view names to template files and renders them:

```php
// Renders: resources/views/products/index.php
echo $views->render('products.index', ['products' => $products]);

// Renders: resources/views/admin/settings.php
echo $views->render('admin.settings', ['tab' => 'general']);
```

### Deferred Rendering

Use `make()` to create a `View` instance that can be passed around and rendered later:

```php
$view = $views->make('emails.welcome', ['user' => $user]);

// Add more data
$view->with('siteName', get_bloginfo('name'));
$view->with(['footer' => $footer, 'year' => date('Y')]);

// Render when ready
$html = $view->render();

// Or use as string (calls render automatically)
echo $view;
```

### Sharing Data

Share data that will be available to all views:

```php
$views->share('siteName', get_bloginfo('name'));
$views->share('currentUser', wp_get_current_user());

// Share multiple values at once
$views->share([
    'version' => '1.0.0',
    'debug' => WP_DEBUG,
]);
```

### Checking View Existence

```php
if ($views->exists('admin.custom-page')) {
    echo $views->render('admin.custom-page');
} else {
    echo $views->render('admin.fallback');
}
```

### PHP Templates

PHP templates receive data as extracted variables:

```php
<!-- resources/views/products/index.php -->
<div class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product">
            <h3><?= esc_html($product->name) ?></h3>
            <p><?= esc_html($product->description) ?></p>
            <span><?= esc_html(wc_price($product->price)) ?></span>
        </div>
    <?php endforeach; ?>
</div>
```

### Twig Templates

Register the Twig engine and use `.twig` templates:

```php
use WPZylos\Framework\Views\Engines\TwigEngine;

$twig = new TwigEngine($views->getBasePath(), '/path/to/cache', false);
$views->addEngine($twig);

// Now .twig files are resolved automatically
echo $views->render('emails.welcome', ['user' => $user]);
```

```twig
{# resources/views/emails/welcome.twig #}
<h1>Welcome, {{ user.name }}!</h1>
<p>Thanks for joining {{ siteName }}.</p>
```

---

### CssHelper — DaisyUI CSS Prefix Helper

The `CssHelper` class generates prefixed CSS class names for DaisyUI components, ensuring style isolation between plugins:

```php
use WPZylos\Framework\Views\CssHelper;

// Injected automatically via container
$css = $app->make(CssHelper::class);

// Generate prefixed class names
echo $css->cls('btn', 'btn-primary');
// Output: 'fcds-btn fcds-btn-primary'

// Get the raw prefix
echo $css->prefix();       // 'fcds-'
echo $css->prefixClean();  // 'fcds'

// Admin root container helpers
echo $css->adminOpen();    // <div id="fcds-admin" class="wrap">
echo $css->adminClose();   // </div>
echo $css->adminRootId();  // 'fcds-admin'
```

### JsMount — Vue.js / React Mount Points

The `JsMount` class renders mount points for Vue.js and React apps with WordPress data passing:

```php
use WPZylos\Framework\Views\JsMount;

$mount = $app->make(JsMount::class);

// Admin page mount point (wrapped in admin root scope)
echo $mount->adminMount('my-vue-app', [
    'nonce'   => wp_create_nonce('wp_rest'),
    'restUrl' => rest_url('myplugin/v1'),
]);
// Outputs a WordPress admin wrapper with data-theme="light".

// Frontend mount point (no admin wrapper)
echo $mount->frontendMount('product-gallery', [
    'products' => $products,
]);

// Shortcode mount point
echo $mount->shortcodeMount('contact-form', [
    'action' => admin_url('admin-ajax.php'),
]);
```

### Vue.js 3 & React 19 Support

WPZylos Views supports both **Vue.js 3** (Options API) and **React 19** (opt-in) for building interactive admin and frontend interfaces:

- **Vue.js 3 Options API** — Use `JsMount::adminMount()` to create a mount point, then mount your Vue app to the rendered element ID
- **React 19** — Opt-in support via the same mount point system; switch your JS entry to `createRoot()` instead of `createApp()`
- **DaisyUI v5 + Tailwind v4** — All components use prefixed DaisyUI classes via `CssHelper` for zero-conflict styling

---

## Related Packages

| Package                                                                | Description            |
| ---------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/KYNetCode/wpzylos-core)         | Application foundation |
| [wpzylos-routing](https://github.com/KYNetCode/wpzylos-routing)   | URL routing            |
| [wpzylos-scaffold](https://github.com/KYNetCode/wpzylos-scaffold) | Plugin template        |

---

## Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## Support the Project

- [GitHub Sponsors](https://github.com/sponsors/KYNetCode)
- [PayPal Donate](https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC)

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with love by [KYNetCode](https://github.com/KYNetCode)**
