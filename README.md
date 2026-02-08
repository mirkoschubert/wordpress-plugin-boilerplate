# WP Plugin Boilerplate

WordPress Feature Plugin Boilerplate for client projects. Provides a modular architecture with a React-based admin interface for managing feature toggles and settings.

## Requirements

- PHP 8.0+
- WordPress 6.0+
- Node.js 18+ / pnpm
- Composer

## Installation

```bash
composer install
pnpm install
pnpm run build
```

Activate the plugin in WordPress under **Plugins > Installed Plugins**.

## Usage

### For New Client Projects

This boilerplate is designed for easy customization via global search & replace. Replace the following placeholders with your project-specific values:

| Placeholder                | Purpose                      | Example                  |
| -------------------------- | ---------------------------- | ------------------------ |
| `WP Plugin Boilerplate`    | Display name                 | `Mustermann Features`    |
| `wp-plugin-boilerplate`    | Slug, text domain            | `mustermann-features`    |
| `WPPluginBoilerplate`      | PHP namespace                | `MustermannFeatures`     |
| `wp_plugin_boilerplate`    | Option keys (snake_case)     | `mustermann_features`    |
| `wpPluginBoilerplateConfig`| JS config object (camelCase) | `mustermannFeaturesConfig` |
| `WP_PLUGIN_BOILERPLATE`    | PHP constants (UPPER_CASE)   | `MUSTERMANN_FEATURES`    |
| `wpbp-`                    | CSS class prefix              | `mf-`                   |

### Admin Interface

After activation, the settings page is available under **Settings > WP Plugin Boilerplate**. The React-based admin app allows you to enable/disable modules and configure individual module settings.

## Architecture

```
wp-plugin-boilerplate.php     # Main plugin file
src/
  Core/
    Plugin.php                # Bootstrapper
    Config.php                # Singleton configuration
    Migration.php             # Version migrations
    helpers.php               # Global utility functions
    Abstracts/                # Module & ModuleService base classes
    Interfaces/               # ModuleInterface, ServiceInterface
    Traits/                   # DependencyChecker, AjaxHandler
  Admin/
    Admin.php                 # Settings page (under Settings menu)
  API/
    RestController.php        # REST API (wp-plugin-boilerplate/v1/)
    Abstracts/
      ModuleController.php    # Base class for module REST controllers
  Builders/
    Abstracts/
      DiviModule.php          # Abstract base for Divi custom modules
      BreakdanceElement.php   # Abstract base for Breakdance elements
    Divi/                     # Divi custom modules
    Breakdance/               # Breakdance custom elements
      PostLoopTabs/           # Example: Post Loop Tabs element
modules/                      # Auto-discovered feature modules
admin-app/                    # React 18 + TypeScript admin interface
```

### Modules

Modules are auto-discovered from the `modules/` directory. Each module consists of a `Module` class (configuration & settings) and a `Service` class (WordPress hooks & functionality).

| Module           | Description                                                  |
| ---------------- | ------------------------------------------------------------ |
| **A11y**         | Accessibility features (skip link, scroll-to-top, keyboard navigation, ARIA support) |
| **Administration** | Admin enhancements (duplicate posts, update email control, media library, SVG/WebP/AVIF support, external links, custom image sizes) |
| **CustomPostTypes** | Example CPT module with Jobs implementation, ACF fields, and taxonomies |
| **FileManager**  | File upload/download management with permission-based access control and frontend shortcode |
| **LocalFonts**   | Local Google Fonts with auto-updates, Gutenberg integration, and global Google Fonts blocking |
| **Login**        | Custom login page styling                                    |
| **Pagespeed**    | Performance optimizations                                    |
| **Privacy**      | Privacy & GDPR features                                     |
| **System**       | Environment badge in admin bar, system info in At a Glance   |
| **UIKit**        | Admin field reference (dev only)                             |
| **Umami**        | Umami Analytics integration                                  |

### Creating a New Module

1. Create a directory under `modules/YourModule/`
2. Add `YourModule.php` extending `WPPluginBoilerplate\Core\Abstracts\Module`
3. Add `Service.php` extending `WPPluginBoilerplate\Core\Abstracts\ModuleService`
4. Optionally add an `assets/` directory for CSS/JS files
5. The module is auto-discovered on next load

### Builder Integration

Abstract base classes are provided for Divi and Breakdance page builder integration:

- **Divi**: Extend `src/Builders/Abstracts/DiviModule.php`, place modules in `src/Builders/Divi/`
- **Breakdance**: Extend `src/Builders/Abstracts/BreakdanceElement.php`, place elements in `src/Builders/Breakdance/`

Builder modules are only loaded when the respective page builder is active.

**Included Breakdance Elements:**
- **PostLoopTabs**: Tabbed post loop with query builder and global block integration

### Global Utilities

**Helper Functions** (`src/Core/helpers.php`):
- `wpbp_format_bytes()` - Format byte size to human-readable
- `wpbp_mime_to_extension()` - Convert MIME type to file extension
- `wpbp_get_plugin_option()` / `wpbp_update_plugin_option()` - Plugin option getters/setters
- `wpbp_is_module_enabled()` - Check if module is active
- `wpbp_sanitize_slug()` - Sanitize strings to slugs
- `wpbp_get_file_icon_class()` - Get Dashicon class for file types
- `wpbp_array_get()` - Safe array key access

**AJAX Handler Trait** (`src/Core/Traits/AjaxHandler.php`):
- Standardized AJAX request verification (nonce + capabilities)
- JSON response helpers
- Input sanitization
- Error logging

## Development

```bash
# Watch mode (auto-rebuild on changes)
pnpm run dev

# Production build
pnpm run build

# Type checking
pnpm run type-check

# Linting
pnpm run lint

# Generate translation files
pnpm run build:translations
```

## REST API

The plugin exposes a REST API at `wp-json/wp-plugin-boilerplate/v1/`:

| Endpoint               | Method     | Description              |
| ---------------------- | ---------- | ------------------------ |
| `/modules`             | `GET`      | List all modules         |
| `/modules/<slug>`      | `GET`      | Get module details       |
| `/modules/<slug>`      | `POST/PUT` | Update module settings   |

All endpoints require `manage_options` capability.

## License

GPL-3.0
