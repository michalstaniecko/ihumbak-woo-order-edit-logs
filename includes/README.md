# Includes Directory

This directory contains all PHP class files for the plugin.

## Structure

### Core Classes (Etap 1 - Completed)

- `class-order-logger.php` - Main logging class (singleton pattern)
  - Plugin initialization
  - Database upgrade checks
  - Admin notices
  
- `class-log-database.php` - Database operations
  - Table creation and schema management
  - Log insertion and retrieval
  - Database versioning system
  - Automatic retention and cleanup methods
  
- `class-hpos-compatibility.php` - HPOS compatibility layer
  - Detection of storage mode (CPT vs HPOS)
  - Universal order access methods
  - Order data extraction for comparison
  - Helper methods for comparing values and arrays
  
### Placeholder Classes (To be implemented in future stages)

- `class-log-tracker.php` - Change tracking (Etap 2)
- `class-log-formatter.php` - Data formatting (Etap 2)
- `class-log-exporter.php` - Data export functionality (Etap 5)

### Admin Classes (To be implemented in Etap 4)

- `admin/class-admin-interface.php` - Admin interface setup
- `admin/class-log-viewer.php` - Log viewing interface
- `admin/class-settings.php` - Settings management
- `admin/views/` - Admin view templates

### Hook Files (To be implemented in Etap 3)

- `hooks/order-hooks.php` - WordPress/WooCommerce order hooks
- `hooks/product-hooks.php` - Product-related hooks
- `hooks/address-hooks.php` - Address change hooks
- `hooks/payment-hooks.php` - Payment method hooks

## Namespace Structure

All classes use the namespace `IHumBak\WooOrderEditLogs\` or sub-namespaces:
- Core classes: `IHumBak\WooOrderEditLogs\`
- Admin classes: `IHumBak\WooOrderEditLogs\Admin\`

## Autoloading

Classes are autoloaded using a PSR-4 compatible autoloader defined in the main plugin file.
Class names are converted from `Snake_Case` to `kebab-case` with `class-` prefix for file names.

Example:
- Class: `Order_Logger` → File: `class-order-logger.php`
- Class: `Admin\Log_Viewer` → File: `admin/class-log-viewer.php`

