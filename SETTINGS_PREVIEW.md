# Settings Page Preview

## WooCommerce Settings > Order Logs Tab

Below is a preview of how the new "Custom Meta Fields Tracking" section appears in the WooCommerce settings page:

---

### General Settings

**☑ Enable Logging**  
Enable automatic order change logging

**☑ Save IP Address**  
Save user IP address with each log entry

**☑ Save User Agent**  
Save browser user agent with each log entry

---

### Log Retention

**☐ Auto Cleanup**  
Automatically delete old logs

**Retention Days:** `90`  
Number of days to keep logs before automatic deletion

---

### Performance

**Logs Per Page:** `20`  
Number of log entries to display per page

---

### 🆕 Custom Meta Fields Tracking

**Custom Meta Fields:**

```
┌─────────────────────────────────────────┐
│ _billing_vat                           │
│ _custom_tax_id                         │
│ _shipping_company_code                 │
│ _internal_order_notes                  │
│                                         │
│                                         │
│                                         │
└─────────────────────────────────────────┘
```

Enter custom meta field names to track (one per line). Example: _billing_vat

**Track changes to custom post meta fields on orders.**

---

### Export Settings

**Date Format:** `Y-m-d H:i:s`  
Date format for exported files

**CSV Separator:** `, (Comma)`  
Choose separator for CSV exports

---

**[Save changes]**

---

## Example Usage

### Before Configuration
No custom fields are tracked - only standard WooCommerce fields are logged.

### After Adding `_billing_vat`

When an order's `_billing_vat` meta field changes from `PL1234567890` to `PL9876543210`, the plugin logs:

```
┌──────────────────────────────────────────────────────────────┐
│ Order Log Entry #12345                                       │
├──────────────────────────────────────────────────────────────┤
│ Date/Time:    2025-10-11 15:30:00                           │
│ Order:        #789                                           │
│ User:         Admin (admin@example.com)                      │
│ Action:       Custom Field Changed                           │
│ Field:        _billing_vat                                   │
│ Old Value:    PL1234567890                                   │
│ New Value:    PL9876543210                                   │
│ IP Address:   192.168.1.1                                    │
│ User Agent:   Mozilla/5.0...                                 │
└──────────────────────────────────────────────────────────────┘
```

### Multiple Fields Example

Configuration:
```
_billing_vat
_custom_tax_id
_internal_notes
```

Result: All three fields are now tracked. Any change to any of these fields will be logged with full details.

---

## Field Name Requirements

✅ **Valid Field Names:**
- `_billing_vat` - With underscore prefix
- `custom_field_name` - Without prefix
- `_order_special_notes` - Multi-word with underscores

❌ **Invalid Field Names:**
- Empty lines (automatically ignored)
- Whitespace-only lines (automatically ignored)

---

## Benefits

🎯 **Flexibility** - Track any custom field by name  
📝 **Simplicity** - Just enter the field name  
🔍 **Visibility** - Full change history  
👤 **Accountability** - Know who changed what  
⚡ **Performance** - Only configured fields are tracked  
🔒 **Security** - No code changes required  

---

## Notes

- Field names are **case-sensitive**
- One field name per line
- Include underscore prefix if your field uses one (e.g., `_billing_vat`)
- Changes are only tracked for **WooCommerce orders**
- Works with both **CPT** and **HPOS** storage modes
- Changes take effect immediately after saving settings
