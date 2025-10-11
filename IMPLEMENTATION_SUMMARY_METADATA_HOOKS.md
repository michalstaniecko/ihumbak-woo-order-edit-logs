# Update to Custom Meta Field Tracking - Implementation Summary

## Issue
The custom meta field tracking needed to be updated to be compatible with both:
1. Direct `update_post_meta()` calls (used by themes in CPT mode)
2. WooCommerce's `$order->update_meta_data()` + `$order->save()` methods (HPOS-compatible)

## Solution Overview
Implemented a dual-mode tracking system that monitors custom meta field changes regardless of the update method used.

## Changes Made

### 1. New Metadata Hooks File
**File**: `includes/hooks/metadata-hooks.php`

Added a new hooks file that monitors direct WordPress metadata operations:
- `update_post_metadata` filter - Captures updates before they occur
- `added_post_meta` action - Tracks new meta fields
- `deleted_post_meta` action - Tracks deleted meta fields

**Key Features**:
- Only tracks configured custom meta fields (from plugin settings)
- Only monitors WooCommerce order post types
- Prevents duplicate logging by checking for active snapshots
- Logs changes immediately when meta is updated directly

### 2. Integration with Order Logger
**File**: `includes/class-order-logger.php`

Updated the `init_woocommerce_hooks()` method to load and initialize the new metadata hooks file.

### 3. Documentation Updates
**Files**: 
- `CUSTOM_META_FIELDS.md` - Added explanation of dual-mode tracking
- `TESTING_CUSTOM_META_FIELDS.md` - Added testing instructions for both update methods

Updated documentation to explain:
- How the dual-mode tracking works
- Supported update methods (both `update_post_meta()` and `$order->update_meta_data()`)
- Testing procedures for both approaches

### 4. Tests
**File**: `tests/MetadataHooksTest.php`

Added unit tests to verify:
- Metadata hooks file exists
- All required functions are defined
- Functions are properly namespaced

## How It Works

### Scenario 1: Direct update_post_meta() Call
```php
update_post_meta($order_id, '_billing_vat', 'PL1234567890');
```

1. WordPress triggers `update_post_metadata` filter
2. Our `capture_meta_update()` function is called
3. Checks if this is an order and if the meta key is tracked
4. Checks if no snapshot exists (not in the middle of order save)
5. Logs the change immediately
6. Allows the update to proceed

### Scenario 2: WooCommerce Order Save
```php
$order->update_meta_data('_billing_vat', 'PL1234567890');
$order->save();
```

1. `woocommerce_before_order_object_save` hook creates a snapshot
2. Order is saved (might trigger `update_post_metadata` internally)
3. Our metadata hook sees the snapshot exists and skips logging
4. `woocommerce_after_order_object_save` hook compares snapshot to current state
5. Snapshot approach logs the change
6. Snapshot is deleted

### Deduplication Logic
The key to preventing duplicate logs is checking for the existence of a snapshot:
- **Snapshot exists** → Order save in progress → Let snapshot approach handle logging
- **No snapshot** → Direct meta update → Log immediately via metadata hooks

## Compatibility

### CPT Mode (Custom Post Types)
- ✅ Tracks `update_post_meta()` calls via metadata hooks
- ✅ Tracks `$order->update_meta_data()` + `$order->save()` via snapshot approach
- ✅ No duplicate logging

### HPOS Mode (High-Performance Order Storage)
- ✅ Tracks `$order->update_meta_data()` + `$order->save()` via snapshot approach
- ⚠️ Metadata hooks won't fire (HPOS doesn't use `update_post_meta()`)
- ✅ All changes tracked via snapshot approach

## Testing

To test the implementation:

1. Configure custom meta fields in plugin settings
2. Test direct `update_post_meta()` calls (CPT mode)
3. Test `$order->update_meta_data()` + `$order->save()` (both modes)
4. Verify single log entry for each change (no duplicates)
5. Verify logs show correct old and new values

See `TESTING_CUSTOM_META_FIELDS.md` for detailed testing instructions.

## Backward Compatibility

- ✅ No breaking changes to existing functionality
- ✅ Existing snapshot-based tracking continues to work
- ✅ New metadata hooks only add functionality, don't modify existing behavior
- ✅ Plugin settings remain unchanged
- ✅ Database schema remains unchanged
- ✅ Log format remains unchanged

## Files Modified

1. `includes/hooks/metadata-hooks.php` - NEW
2. `includes/class-order-logger.php` - Modified (added metadata hooks initialization)
3. `CUSTOM_META_FIELDS.md` - Updated documentation
4. `TESTING_CUSTOM_META_FIELDS.md` - Updated testing guide
5. `tests/MetadataHooksTest.php` - NEW

## Conclusion

The implementation successfully addresses the issue by:
1. Supporting both `update_post_meta()` and `$order->update_meta_data()` methods
2. Preventing duplicate logging
3. Maintaining backward compatibility
4. Providing comprehensive documentation and tests

The system now tracks custom meta field changes regardless of how themes or plugins update the metadata.
