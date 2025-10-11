# Manual Testing Guide for Custom Meta Fields Tracking

## Prerequisites

Before testing, make sure:
1. WordPress and WooCommerce are installed and activated
2. The iHumBak Order Edit Logs plugin is activated
3. You have admin access to the WordPress admin panel

## Test Scenario: Tracking _billing_vat Field

### Step 1: Configure Custom Meta Fields

1. Log in to WordPress admin panel
2. Navigate to **WooCommerce > Settings**
3. Click on the **Order Logs** tab
4. Scroll down to **Custom Meta Fields Tracking** section
5. In the **Custom Meta Fields** textarea, enter:
   ```
   _billing_vat
   ```
6. Click **Save changes**

### Step 2: Create or Edit an Order

1. Navigate to **WooCommerce > Orders**
2. Either create a new order or open an existing order
3. If the order doesn't have a `_billing_vat` field yet, you may need to add it using code or a plugin

### Step 3: Add Custom Meta Field Value (if needed)

If you need to manually add the `_billing_vat` meta field to an order, you can use a snippet like this in your theme's functions.php or a custom plugin:

```php
// Add this temporarily to add a custom meta field to an order
add_action('admin_init', function() {
    if (isset($_GET['test_add_vat'])) {
        $order_id = absint($_GET['order_id']);
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data('_billing_vat', 'PL1234567890');
                $order->save();
                echo '<div class="notice notice-success"><p>VAT number added!</p></div>';
            }
        }
    }
});
```

Then visit: `wp-admin/?test_add_vat=1&order_id=123` (replace 123 with actual order ID)

### Step 4: Modify the Custom Meta Field

You can modify the custom meta field using either method:

**Method 1: Direct update_post_meta() (simulates theme behavior in CPT mode)**
```php
// Add this temporarily to your theme's functions.php or a custom plugin
add_action('admin_init', function() {
    if (isset($_GET['test_update_vat'])) {
        $order_id = absint($_GET['order_id']);
        if ($order_id) {
            // Direct meta update - plugin will track this
            update_post_meta($order_id, '_billing_vat', 'PL9876543210');
            echo '<div class="notice notice-success"><p>VAT number updated via update_post_meta()!</p></div>';
        }
    }
});
```

**Method 2: WooCommerce order save (works in both CPT and HPOS)**
```php
// Add this temporarily to your theme's functions.php or a custom plugin
add_action('admin_init', function() {
    if (isset($_GET['test_update_vat_wc'])) {
        $order_id = absint($_GET['order_id']);
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data('_billing_vat', 'PL9876543210');
                $order->save();
                echo '<div class="notice notice-success"><p>VAT number updated via WooCommerce methods!</p></div>';
            }
        }
    }
});
```

Test both methods to verify tracking works correctly:
- Visit: `wp-admin/?test_update_vat=1&order_id=123` (for direct update_post_meta)
- Visit: `wp-admin/?test_update_vat_wc=1&order_id=123` (for WooCommerce methods)

### Step 5: Verify the Log Entry

1. In the order edit screen, look for the **Order Logs** or **History Changes** section
2. You should see a new log entry with:
   - **Action Type**: Custom Field Changed
   - **Field Name**: _billing_vat
   - **Old Value**: PL1234567890
   - **New Value**: PL9876543210
   - **User**: Your username
   - **Timestamp**: Current date and time

### Step 6: View All Logs

1. Navigate to **WooCommerce > Order Logs** (if available in admin menu)
2. Filter by **Action Type**: Custom Field Changed
3. Verify the log entry appears in the list

## Expected Results

✅ The settings page should show the Custom Meta Fields Tracking section
✅ The custom meta field value should be tracked in the snapshot before save
✅ Changes to the custom meta field should be detected and logged
✅ The log entry should show the correct old and new values
✅ The log entry should use the action type 'custom_field_changed'
✅ The field name in the log should match the meta key exactly (_billing_vat)

## Testing Multiple Fields

To test multiple custom meta fields:

1. In the settings, enter multiple field names (one per line):
   ```
   _billing_vat
   _custom_tax_id
   _internal_notes
   ```
2. Modify any of these fields on an order
3. Verify that each field's changes are logged separately

## Troubleshooting

### Logs are not appearing

1. Check that logging is enabled in **WooCommerce > Settings > Order Logs > General Settings**
2. Verify that the custom meta field name is spelled correctly (case-sensitive, including underscores)
3. Check that the order was actually saved after the change
4. Look for PHP errors in the error log

### Field not found

1. Make sure the meta field actually exists on the order
2. Use a plugin like "Show Current Template" or "Query Monitor" to inspect post meta
3. Verify the field name matches exactly (including the underscore prefix if present)

## Code Verification

You can verify the implementation by checking these files:

1. **includes/admin/class-settings.php** - Settings configuration
2. **includes/class-hpos-compatibility.php** - Custom meta extraction
3. **includes/hooks/order-hooks.php** - Change detection and logging

## Database Verification

To verify directly in the database:

```sql
-- View order logs for a specific order
SELECT * FROM wp_woocommerce_order_logs 
WHERE order_id = 123 
AND action_type = 'custom_field_changed'
ORDER BY created_at DESC;

-- View all custom field changes
SELECT * FROM wp_woocommerce_order_logs 
WHERE action_type = 'custom_field_changed'
ORDER BY created_at DESC
LIMIT 10;
```

(Note: Table name may vary based on your WordPress prefix)
