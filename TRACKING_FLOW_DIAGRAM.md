# Custom Meta Field Tracking - Flow Diagram

## Tracking Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    Custom Meta Field Update                      │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
                    ┌───────────────────────┐
                    │  Which Update Method? │
                    └───────────────────────┘
                                │
                ┌───────────────┴───────────────┐
                ▼                               ▼
    ┌────────────────────┐          ┌────────────────────┐
    │ update_post_meta() │          │ $order->update_    │
    │   (CPT mode)       │          │  meta_data()       │
    └────────────────────┘          │ + $order->save()   │
                │                    └────────────────────┘
                │                               │
                ▼                               ▼
    ┌────────────────────┐          ┌────────────────────┐
    │ update_post_       │          │ woocommerce_       │
    │  metadata filter   │          │  before_order_     │
    │                    │          │  object_save       │
    └────────────────────┘          └────────────────────┘
                │                               │
                │                               ▼
                │                    ┌────────────────────┐
                │                    │ Create Snapshot    │
                │                    └────────────────────┘
                │                               │
                ▼                               ▼
    ┌────────────────────┐          ┌────────────────────┐
    │ capture_meta_      │          │ Order Saved        │
    │  update()          │          │ (triggers update_  │
    │                    │          │  post_metadata)    │
    └────────────────────┘          └────────────────────┘
                │                               │
                │                               ▼
                │                    ┌────────────────────┐
                │                    │ capture_meta_      │
                │                    │  update()          │
                │                    └────────────────────┘
                │                               │
                ▼                               ▼
    ┌────────────────────┐          ┌────────────────────┐
    │ Check:             │          │ Check:             │
    │ - Is order?        │          │ - Is order? ✓      │
    │ - Is tracked?      │          │ - Is tracked? ✓    │
    │ - Has snapshot?    │          │ - Has snapshot? ✓  │
    └────────────────────┘          └────────────────────┘
                │                               │
                │ No snapshot                   │ Snapshot exists
                ▼                               ▼
    ┌────────────────────┐          ┌────────────────────┐
    │ LOG CHANGE         │          │ SKIP (let snapshot │
    │ (metadata hooks)   │          │  approach handle)  │
    └────────────────────┘          └────────────────────┘
                                                │
                                                ▼
                                    ┌────────────────────┐
                                    │ woocommerce_       │
                                    │  after_order_      │
                                    │  object_save       │
                                    └────────────────────┘
                                                │
                                                ▼
                                    ┌────────────────────┐
                                    │ Compare Snapshot   │
                                    │ to Current State   │
                                    └────────────────────┘
                                                │
                                                ▼
                                    ┌────────────────────┐
                                    │ LOG CHANGE         │
                                    │ (snapshot approach)│
                                    └────────────────────┘
```

## Key Points

### Metadata Hooks Approach (Direct Updates)
- **When**: `update_post_meta()` is called directly
- **How**: `update_post_metadata` filter captures the change
- **Check**: Only logs if no snapshot exists (not in order save operation)
- **Result**: Immediate logging of the change

### Snapshot Approach (WooCommerce Save)
- **When**: `$order->update_meta_data()` + `$order->save()` is called
- **How**: Creates snapshot before save, compares after save
- **Check**: Compares all custom meta fields in snapshot vs current state
- **Result**: Logs all changes detected after save

### Deduplication Logic
When `$order->save()` triggers `update_post_meta()` internally:
1. Snapshot is created before save
2. `update_post_metadata` filter is triggered during save
3. Metadata hook sees snapshot exists
4. Metadata hook skips logging
5. Snapshot approach logs the change after save
6. **Result**: Single log entry (no duplicates)

## Compatibility Matrix

| Storage Mode | update_post_meta() | Direct Metadata API | $order->update_meta_data() |
|--------------|-------------------|---------------------|----------------------------|
| CPT          | ✅ Metadata Hooks | ✅ Metadata Hooks   | ✅ Snapshot Approach      |
| HPOS         | N/A (not used)    | ✅ Metadata Hooks   | ✅ Snapshot Approach      |

## Code Flow Example

### Scenario 1: Theme uses update_post_meta()
```php
// Theme code
update_post_meta($order_id, '_billing_vat', 'PL1234567890');

// Plugin behavior
// 1. update_post_metadata filter triggered
// 2. capture_meta_update() checks: is order? ✓, is tracked? ✓, has snapshot? ✗
// 3. Logs change immediately
// 4. Returns null to allow update
```

### Scenario 2: Theme uses WooCommerce methods
```php
// Theme code
$order = wc_get_order($order_id);
$order->update_meta_data('_billing_vat', 'PL1234567890');
$order->save();

// Plugin behavior
// 1. woocommerce_before_order_object_save creates snapshot
// 2. Order saves (internally may call update_post_meta)
// 3. update_post_metadata filter triggered (if CPT mode)
// 4. capture_meta_update() checks: has snapshot? ✓
// 5. Skips logging (snapshot will handle it)
// 6. woocommerce_after_order_object_save compares snapshot
// 7. Logs change via snapshot approach
```

### Scenario 3: Direct metadata update in HPOS mode
```php
// Plugin/theme code in HPOS mode
update_metadata('wc_order', $order_id, '_billing_vat', 'PL1234567890');

// Plugin behavior
// 1. update_metadata filter triggered (universal metadata filter)
// 2. capture_hpos_meta_update() checks: is order? ✓, is tracked? ✓, has snapshot? ✗
// 3. Logs change immediately
// 4. Returns null to allow update
```
