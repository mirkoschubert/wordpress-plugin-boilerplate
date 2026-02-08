# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
