# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-02-08

### Added

#### Core Infrastructure

- **Global Helper Functions** (`src/Core/helpers.php`) - 8 utility functions for plugin-wide use:
  - `wpbp_format_bytes()` - Format byte size to human-readable format
  - `wpbp_mime_to_extension()` - Convert MIME type to file extension
  - `wpbp_get_plugin_option()` / `wpbp_update_plugin_option()` - Plugin option getters/setters
  - `wpbp_is_module_enabled()` - Check if module is active
  - `wpbp_sanitize_slug()` - Sanitize strings to slugs
  - `wpbp_get_file_icon_class()` - Get Dashicon class for file types
  - `wpbp_array_get()` - Safe array key access with default values
- **AJAX Handler Trait** (`src/Core/Traits/AjaxHandler.php`) - Standardized AJAX handling:
  - Request verification (nonce + capability checks)
  - JSON response helpers (`send_json_success()`, `send_json_error()`)
  - Input sanitization with type validation
  - Error logging with context

#### New Modules

- **FileManager** - Complete file management system with frontend integration:
  - Custom Post Type for files with metadata
  - AJAX-based upload/download/edit/delete operations
  - Permission-based access control via WordPress capabilities
  - Configurable file areas (e.g., "General Files", "Downloads")
  - Frontend shortcode: `[wpbp_files area="general" show_upload="true"]`
  - Upload progress bar with visual feedback
  - File type icons and size formatting
  - Modal editing interface
  - Honeypot spam protection
  - Complete styling with responsive design
- **CustomPostTypes** - Example CPT module demonstrating best practices:
  - Jobs custom post type as reference implementation
  - Programmatic ACF field registration (ensures translatability)
  - Two taxonomies: Job Category (hierarchical), Job Location (non-hierarchical)
  - 9 ACF fields: Start date, application deadline, job type, experience level, salary range, remote work options, contact email, application URL
  - Conditional ACF loading (only if ACF plugin is available)
  - Fully configurable via admin UI (menu position, archive, visibility)
  - Gutenberg editor support
  - Comprehensive README as developer guide

#### Module Enhancements

- **Administration Module**:
  - Custom image sizes registration via admin UI
  - Repeater field for defining custom image sizes (name, width, height, crop)
  - Automatic registration with `add_image_size()`
- **LocalFonts Module**:
  - Gutenberg editor font registration via `theme.json` API (WordPress 5.9+)
  - Fonts available in block editor font picker
  - Global Google Fonts blocking (works with all themes/plugins, not just Divi)
  - Dequeues Google Fonts stylesheets from any source
  - Removes Google Fonts preconnect/prefetch links from HTML head
  - "Disable Google Fonts Globally" toggle in admin settings

#### Builder Elements

- **PostLoopTabs Breakdance Element** (`src/Builders/Breakdance/PostLoopTabs/`) - Example Breakdance custom element:
  - Tabbed post loop with WordPress query builder
  - Global block integration for tab content
  - Dynamic tab names
  - Category: "WP Plugin Boilerplate"
  - Fully documented element structure as template

### Changed

- **LocalFonts admin setting** label changed from "Disable Divi Google Fonts" to "Disable Google Fonts Globally" to reflect expanded functionality
- Module structure now supports more complex implementations with subdirectories and helper classes

### Developer Notes

- All new modules follow established patterns (Module + Service classes)
- ACF fields are registered programmatically for translatability
- AJAX operations use standardized trait for consistency
- File operations include comprehensive error handling and validation
- All customer-specific references removed (generalized implementations)
- Code demonstrates best practices for WordPress plugin development

## [1.0.0] - 2026-02-08

### Added

- Initial release as WordPress plugin boilerplate (adapted from Divi Child Theme v3.0.0)
- Modular architecture with auto-discovery of feature modules
- React 18 + TypeScript admin interface under Settings menu
- REST API for module management (`wp-plugin-boilerplate/v1/`)
- Singleton Config class for centralized options management
- Version migration system for future upgrades
- Abstract base classes for Divi and Breakdance page builder integration
- DependencyChecker trait for WordPress/plugin/builder version constraints
- Generic placeholder naming for easy per-client customization via search & replace

#### Modules

- **A11y** - Accessibility features: skip link, scroll-to-top, keyboard navigation, focus management, ARIA support, screenreader fixes, animation control, text selection colors, link underlines, slider navigation spacing
- **Administration** - Admin enhancements: duplicate posts, disable auto-update emails, media library infinite scroll, SVG upload support (with sanitization), WebP/AVIF upload support, automatic hyphenation, external links in new tab with configurable rel attributes
- **LocalFonts** - Automatically localize Google Fonts
- **Login** - Custom login page styling
- **Pagespeed** - Performance optimizations
- **Privacy** - Privacy & GDPR features
- **System** - Environment badge in admin bar (Local/Dev/Staging/Live), search visibility warning, system summary in At a Glance dashboard widget (PHP/WP version, image format support)
- **UIKit** - Admin field type reference (dev only)
- **Umami** - Umami Analytics integration

#### Build Stack

- PHP 8.0+ with Composer (PSR-4 autoloading, SVG sanitization via `enshrined/svg-sanitize`)
- Webpack 5 with TypeScript, React 18, Stylus
- WordPress component externals (`@wordpress/element`, `@wordpress/components`, `@wordpress/api-fetch`, `@wordpress/i18n`, `@wordpress/icons`)
- ESLint + Prettier for code quality
