# Custom Meta Fields Tracking

## Overview

This plugin now supports tracking changes to custom post meta fields on WooCommerce orders. This feature allows you to monitor specific custom fields that are important to your business.

## Configuration

### Adding Custom Meta Fields to Track

1. Navigate to **WooCommerce > Settings > Order Logs** in your WordPress admin
2. Scroll to the **Custom Meta Fields Tracking** section
3. In the **Custom Meta Fields** textarea, enter the meta field names you want to track (one per line)
4. Click **Save changes**

### Example Configuration

```
_billing_vat
_custom_field_name
_another_meta_field
```

## How It Works

Once configured, the plugin tracks custom meta field changes using two complementary approaches:

### 1. Snapshot-Based Tracking
When an order is saved using WooCommerce's save methods (e.g., `$order->update_meta_data()` + `$order->save()`):

1. **Store snapshots** of the custom meta field values before the order is saved
2. **Compare values** after the order is saved to detect changes
3. **Log changes** with the action type `custom_field_changed`

### 2. Direct Meta Update Tracking
When custom meta fields are updated directly using WordPress functions (e.g., `update_post_meta()`):

1. **Monitor metadata changes** through WordPress hooks
2. **Capture old and new values** before the update occurs
3. **Log changes** immediately with the action type `custom_field_changed`

This dual approach ensures that custom meta field changes are tracked regardless of how your theme or plugins update the metadata.

**Display logs** in the order logs viewer showing old and new values

## Technical Details

### Action Type

Changes to custom meta fields are logged with the action type: `custom_field_changed`

### Field Names

The field name in the log will be the exact meta key name (e.g., `_billing_vat`)

### Log Format

Each log entry includes:
- **Order ID**: The ID of the order that was modified
- **Action Type**: `custom_field_changed`
- **Field Name**: The meta key that changed (e.g., `_billing_vat`)
- **Old Value**: The previous value of the meta field
- **New Value**: The new value of the meta field
- **User**: Who made the change
- **Timestamp**: When the change occurred

## Use Cases

### Example: VAT Number Tracking

If you have a custom field `_billing_vat` for storing customer VAT numbers:

1. Add `_billing_vat` to the Custom Meta Fields setting
2. When an order's VAT number is changed, the plugin will log:
   - Old VAT number
   - New VAT number
   - Who made the change
   - When the change was made

### Example: Custom Order Notes

Track changes to custom internal notes or order metadata that your team maintains.

## Compatibility

This feature is compatible with both:
- **Traditional WooCommerce storage** (Custom Post Types - CPT mode)
- **High-Performance Order Storage (HPOS)**

The plugin automatically detects which storage method is in use and handles the meta fields accordingly.

### Supported Update Methods

The plugin tracks custom meta field changes regardless of the method used:

1. **WordPress native functions** (CPT mode):
   ```php
   update_post_meta( $order_id, '_billing_vat', $vat_number );
   ```

2. **WooCommerce order methods** (works in both CPT and HPOS):
   ```php
   $order->update_meta_data( '_billing_vat', $vat_number );
   $order->save();
   ```

Both approaches are fully supported and will be properly tracked.

## Notes

- Meta field names are case-sensitive
- Include the underscore prefix if your meta field uses one (e.g., `_billing_vat`)
- Empty or whitespace-only lines in the configuration are automatically ignored
- Changes are only tracked for orders, not for other post types
