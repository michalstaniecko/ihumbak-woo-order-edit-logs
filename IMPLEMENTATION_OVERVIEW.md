# Implementation Overview

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    WooCommerce Settings                          │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Custom Meta Fields Tracking                              │   │
│  │                                                           │   │
│  │ Custom Meta Fields:                                       │   │
│  │ ┌───────────────────────────────────────────────────┐    │   │
│  │ │ _billing_vat                                       │    │   │
│  │ │ _custom_tax_id                                     │    │   │
│  │ │ _internal_notes                                    │    │   │
│  │ │                                                     │    │   │
│  │ └───────────────────────────────────────────────────┘    │   │
│  │                                                           │   │
│  │ Enter custom meta field names to track (one per line).   │   │
│  │ Example: _billing_vat                                     │   │
│  │                                                           │   │
│  │ [Save changes]                                            │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow

```
Order Edit Flow:
┌──────────────────┐
│ User edits order │
└────────┬─────────┘
         │
         ▼
┌────────────────────────────────────────────┐
│ woocommerce_before_order_object_save hook  │
│ • store_order_snapshot()                   │
│   - Get current order data                 │
│   - Get configured custom meta fields      │
│   - Extract custom meta values             │
│   - Store in transient (10 min expiry)     │
└────────┬───────────────────────────────────┘
         │
         ▼
┌────────────────────────┐
│ Order is saved         │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────────────────────────┐
│ woocommerce_after_order_object_save hook   │
│ • detect_order_changes()                   │
│   - Get snapshot from transient            │
│   - Get current order data                 │
│   - Compare custom_meta arrays             │
│   - Log changes via Order_Logger           │
│   - Delete snapshot transient              │
└────────┬───────────────────────────────────┘
         │
         ▼
┌────────────────────────┐
│ Log entry created      │
│ • action_type:         │
│   custom_field_changed │
│ • field_name:          │
│   _billing_vat         │
│ • old_value: PL123...  │
│ • new_value: PL987...  │
└────────────────────────┘
```

## Code Components

```
Settings (class-settings.php)
├── get_custom_meta_fields()
│   └── Parse textarea input into array of field names
│
HPOS_Compatibility (class-hpos-compatibility.php)
├── get_order_data_for_comparison()
│   ├── Extract standard order data
│   └── get_custom_meta_data()
│       ├── Get configured fields from Settings
│       └── Extract meta values from order
│
Order Hooks (order-hooks.php)
├── store_order_snapshot()
│   └── Call HPOS_Compatibility::get_order_data_for_comparison()
│
├── detect_order_changes()
│   ├── Compare snapshot vs current data
│   ├── Detect custom_meta changes
│   │   ├── Modified fields
│   │   ├── New fields
│   │   └── Removed fields
│   └── Log via Order_Logger::log_change()
│
└── Order_Logger
    └── Saves to database with action_type='custom_field_changed'
```

## Example Log Entry

```json
{
  "log_id": 12345,
  "order_id": 789,
  "user_id": 1,
  "user_display_name": "Admin",
  "timestamp": "2025-10-11 15:30:00",
  "action_type": "custom_field_changed",
  "field_name": "_billing_vat",
  "old_value": "PL1234567890",
  "new_value": "PL9876543210",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0..."
}
```

## Configuration Steps

1. **Enable Feature**
   - Go to WooCommerce > Settings > Order Logs
   - Find "Custom Meta Fields Tracking" section
   
2. **Add Fields**
   - Enter meta field names (one per line)
   - Field names are case-sensitive
   - Include underscore prefix if applicable
   
3. **Save Settings**
   - Click "Save changes"
   
4. **Edit Order**
   - Change any configured custom meta field
   - Plugin automatically captures and logs the change

## Benefits

✅ **Flexible** - Track any custom meta field by name
✅ **User-friendly** - Simple textarea configuration
✅ **Comprehensive** - Captures all changes (add, modify, remove)
✅ **Auditable** - Full change history with user attribution
✅ **HPOS Compatible** - Works with both storage modes
✅ **Performance** - Only tracks configured fields
