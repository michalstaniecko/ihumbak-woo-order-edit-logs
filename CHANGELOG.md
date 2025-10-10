# Changelog

All notable changes to the iHumBak - WooCommerce Order Edit Logs plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial project structure
- Development environment configuration
- Git and EditorConfig setup
- PHPUnit configuration
- PHP_CodeSniffer configuration for WordPress Coding Standards
- Composer setup for dependency management
- **Etap 1: Podstawowa Infrastruktura**
  - Main plugin file (`ihumbak-woo-order-edit-logs.php`) with initialization and autoloading
  - Uninstall script (`uninstall.php`) for cleanup
  - PSR-4 autoloader implementation
  - Complete directory structure per SPECIFICATION.md
  - Core classes:
    - `class-order-logger.php` - Main plugin class (singleton pattern)
    - `class-log-database.php` - Database operations with schema creation
    - `class-hpos-compatibility.php` - HPOS/CPT abstraction layer
  - Placeholder classes for future stages (Log_Tracker, Log_Formatter, Log_Exporter)
  - Admin placeholder classes (Admin_Interface, Log_Viewer, Settings)
  - Hook files (order, product, address, payment)
  - Structure unit tests
  - Requirements checking (WordPress, WooCommerce, PHP versions)
  - Database table schema matching SPECIFICATION.md
  - Database versioning system for upgrades
  - HPOS storage mode detection
  - Universal order access methods
  - Helper methods for comparing order states

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [1.0.0] - TBD

### Planned Features
- Complete logging system for WooCommerce order changes
- Admin interface for viewing logs
- CSV export functionality
- HPOS (High-Performance Order Storage) compatibility
- Multi-user tracking with detailed user information
- Advanced filtering and search capabilities

---

[Unreleased]: https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs/releases/tag/v1.0.0
