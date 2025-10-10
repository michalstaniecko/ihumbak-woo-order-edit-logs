# Development Setup Guide

This guide will help you set up a local development environment for the iHumBak WooCommerce Order Edit Logs plugin.

## Prerequisites

- PHP 7.4 or higher (8.0+ recommended)
- Composer
- Local WordPress installation (5.8+)
- WooCommerce plugin (6.0+)
- Git

## Initial Setup

### 1. Install Dependencies

Run Composer to install all development dependencies:

```bash
composer install
```

This will install:
- PHPUnit for testing
- PHP_CodeSniffer with WordPress Coding Standards
- PHPStan for static analysis
- PHPUnit Polyfills for compatibility

### 2. WordPress Setup

1. Install WordPress locally (you can use Local by Flywheel, XAMPP, or any other local WordPress environment)
2. Install and activate WooCommerce
3. Create a symbolic link or copy this plugin to your WordPress plugins directory:

```bash
# Option 1: Symbolic link (recommended for development)
ln -s /path/to/ihumbak-woo-order-edit-logs /path/to/wordpress/wp-content/plugins/ihumbak-woo-order-edit-logs

# Option 2: Clone directly into plugins directory
cd /path/to/wordpress/wp-content/plugins
git clone https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs.git
```

## Development Commands

### Linting

Check code against WordPress Coding Standards:

```bash
composer run lint
```

Auto-fix coding standard issues:

```bash
composer run lint:fix
```

### Static Analysis

Run PHPStan for static code analysis:

```bash
composer run analyze
```

### Testing

Run PHPUnit tests:

```bash
composer run test
```

Run tests with coverage report:

```bash
vendor/bin/phpunit --coverage-html coverage
```

## Editor Configuration

The project includes an `.editorconfig` file that defines coding styles. Make sure your editor supports EditorConfig:

- **VS Code**: Install the "EditorConfig for VS Code" extension
- **PHPStorm**: Built-in support, enable in Settings → Editor → Code Style
- **Sublime Text**: Install the "EditorConfig" package

## Code Standards

This project follows:
- WordPress Coding Standards
- PSR-4 autoloading
- PHP 7.4+ syntax features
- Semantic Versioning

## Git Workflow

1. Create a feature branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes and commit:
   ```bash
   git add .
   git commit -m "Description of changes"
   ```

3. Push and create a pull request:
   ```bash
   git push origin feature/your-feature-name
   ```

## Next Steps

After completing the setup:
1. Review the [SPECIFICATION.md](SPECIFICATION.md) for plugin architecture
2. Check the [WORKING_PLAN.md](WORKING_PLAN.md) for development roadmap
3. Start with Etap 1: Podstawowa Infrastruktura

## Troubleshooting

### Composer Install Fails

If `composer install` fails, try:
```bash
composer install --ignore-platform-reqs
```

### PHPUnit Cannot Find Tests

Make sure the `tests/bootstrap.php` file is present and Composer dependencies are installed.

### WordPress Coding Standards Not Found

Ensure WPCS is installed via Composer:
```bash
composer require --dev wp-coding-standards/wpcs
```

## Support

For issues or questions:
- Open an issue on [GitHub](https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs/issues)
- Review the documentation in [SPECIFICATION.md](SPECIFICATION.md)
