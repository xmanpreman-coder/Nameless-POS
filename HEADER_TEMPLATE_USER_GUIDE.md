# 📥 How to Download Header Template - User Guide

## Quick Access Points

### Option 1: From Products List (Recommended)
**Path:** Menu > Products

```
┌─────────────────────────────────────┐
│     Add Product    Add via CSV       │
│     Update Multiple    Download...   │
│     ↓ Download Templates ↓           │
│                                     │
│  ✨ Header Only (.xlsx)              │ ← Click Here
│     Empty template with headers     │
│                                     │
│  📊 Excel Template (.xlsx)           │
│     Template with 11 samples        │
│                                     │
│  📄 CSV Template                     │
│     Excel-compatible format         │
└─────────────────────────────────────┘
```

**Steps:**
1. Go to **Products** menu
2. Click **"Download Templates"** dropdown button (blue, with download icon)
3. Select **"Header Only (.xlsx)"**
4. File downloads as `product-header-template.xlsx`

---

### Option 2: From Import Page
**Path:** Menu > Products > "Add Products via CSV" or "Update Multiple Products"

```
┌─────────────────────────────────────┐
│  Get Started - Download Template    │
│                                     │
│  ┌───────────────────────────────┐  │
│  │ 📋 Header Only (.xlsx)        │  │ ← Click Here
│  │ Empty template - fill data    │  │
│  └───────────────────────────────┘  │
│                                     │
│  ┌───────────────────────────────┐  │
│  │ 📊 Excel Template (.xlsx)     │  │
│  │ Enhanced with samples         │  │
│  └───────────────────────────────┘  │
│                                     │
│  ┌───────────────────────────────┐  │
│  │ 📄 CSV Template               │  │
│  │ Excel-compatible format       │  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

**Steps:**
1. Go to **Products** menu
2. Click **"Add Products via CSV"** button
3. Scroll to "Get Started - Download Template" section
4. Click **"Header Only (.xlsx)"** button
5. File downloads as `product-header-template.xlsx`

---

### Option 3: From Products List View (Alternative)
**Path:** Menu > Products > Download Template dropdown

```
┌──────────────────────────────────┐
│  Download Template ▼             │
│  ├─ Header Only                  │ ← Click Here
│  └─ With Samples                 │
└──────────────────────────────────┘
```

**Steps:**
1. Go to **Products** menu (alternative view)
2. Click **"Download Template"** dropdown button
3. Select **"Header Only"**
4. File downloads as `product-header-template.xlsx`

---

## What You Get

### File Contents
The downloaded Excel file contains:

```
┌──────────────┬────────┬──────┬────────┬────────┬────┬────────┬──────────┬──────┐
│ Category     │ Brand  │ SKU  │ GTIN   │ Name   │Cos│ Price  │Quantity  │ Unit │
├──────────────┼────────┼──────┼────────┼────────┼────┼────────┼──────────┼──────┤
│              │        │      │        │        │    │        │          │      │
│              │        │      │        │        │    │        │          │      │
│              │        │      │        │        │    │        │          │      │

(Empty rows ready for your data)
```

**Columns (9 total):**
1. **Category** - Product category (e.g., Electronics, Books)
2. **Brand** - Product brand (e.g., Samsung, Sony)
3. **SKU** - Stock keeping unit / Product code
4. **GTIN** - Barcode number / EAN code
5. **Name** - Product name / Description
6. **Cost** - Cost price (decimal: 50000.00)
7. **Price** - Selling price (decimal: 75000.00)
8. **Quantity** - Stock quantity (whole numbers: 100)
9. **Unit** - Measurement unit (pcs, kg, meter, etc.)

---

## How to Use the Template

### Step 1: Download
Click one of the download buttons shown above.  
File saves as: `product-header-template.xlsx`

### Step 2: Open in Excel
- Double-click the downloaded file
- Or right-click → Open with → Excel

### Step 3: Fill Your Data
```
┌──────────────┬────────┬──────────┬────────────────┬──────────────┐
│ Category     │ Brand  │ SKU      │ GTIN           │ Name         │
├──────────────┼────────┼──────────┼────────────────┼──────────────┤
│ Electronics  │Samsung │ PRD001   │ 1234567890123  │Laptop 15 inch│
│ Books        │ Gramedia│ PRD002  │ 1234567890124  │Novel Book    │
│ Fashion      │ Nike   │ PRD003   │ 1234567890125  │Sports Shoes  │
└──────────────┴────────┴──────────┴────────────────┴──────────────┘

┌─────────┬────────┬──────────┬──────┐
│ Cost    │ Price  │ Quantity │ Unit │
├─────────┼────────┼──────────┼──────┤
│ 5000000 │ 7500000│ 100      │ pcs  │
│ 50000   │ 75000  │ 50       │ pcs  │
│ 150000  │ 250000 │ 30       │ pcs  │
└─────────┴────────┴──────────┴──────┘
```

### Step 4: Save the File
Press `Ctrl+S` or File → Save

### Step 5: Upload to System
1. Go back to **Products** menu
2. Click **"Add Products via CSV"**
3. Click "Choose File" / "Browse"
4. Select your filled Excel file
5. Click **"Import Products"**

---

## Data Format Requirements

| Column | Format | Example | Notes |
|--------|--------|---------|-------|
| **Category** | Text | Electronics | Must match existing category |
| **Brand** | Text | Samsung | Optional but must exist |
| **SKU** | Text | PRD-001 | Unique identifier (required for updates) |
| **GTIN** | Text | 1234567890123 | 13-digit barcode (optional) |
| **Name** | Text | Laptop 15" | Product display name |
| **Cost** | Number | 50000.00 | Use decimal point (.) not comma |
| **Price** | Number | 75000.00 | Use decimal point (.) not comma |
| **Quantity** | Integer | 100 | No decimals, whole numbers only |
| **Unit** | Text | pcs | pcs, kg, meter, box, etc. |

---

## Important Notes

### ✅ DO:
- Use the header template for new bulk entries
- Keep the column order as provided
- Use proper number formats (50000.00 not 50.000,00)
- Match existing categories and brands
- Use consistent product codes (SKU)

### ❌ DON'T:
- Change the header row names
- Add or remove columns
- Mix up the column order
- Leave required fields empty
- Use special characters in category names

---

## Common Questions

### Q: Can I edit the column headers?
**A:** No, headers must remain as provided to match database structure.

### Q: Can I add more columns?
**A:** Additional columns will be ignored during import. Only the 9 standard columns are processed.

### Q: What if I make a mistake?
**A:** You can re-download the template and try again. Previous failed imports don't affect the system.

### Q: How many products can I import at once?
**A:** No strict limit, but files larger than 10MB will be rejected for performance reasons.

### Q: Will it overwrite existing products?
**A:** Only if you use SKU column and select "Update Existing" mode. Use "Add New" mode to only add new products.

---

## Troubleshooting

### File won't download
- Check internet connection
- Try a different browser
- Clear browser cache (Ctrl+Shift+Delete)
- Disable browser extensions that block downloads

### File won't open
- Make sure Excel or LibreOffice is installed
- Try right-click → Open with → Excel
- File is standard .xlsx format, compatible with most spreadsheet apps

### Import fails with "Columns don't match"
- Download a fresh template
- Ensure column order matches exactly
- Check that headers are spelled correctly

### Numbers formatted incorrectly
- Use decimal point (.) not comma (,)
- Costs/Prices: 50000.00 (not 50.000,00)
- Quantities: 100 (not 100.0 or 100,0)

---

## Examples

### Example 1: Adding Electronics Products
```
Category    │ Brand    │ SKU     │ GTIN           │ Name
Electronics │ Sony     │ ELEC001 │ 1234567890001  │ Headphone XM4
Electronics │ Apple    │ ELEC002 │ 1234567890002  │ iPhone 14 Pro
Electronics │ Samsung  │ ELEC003 │ 1234567890003  │ Galaxy S23
```

### Example 2: Adding Mixed Products
```
Category      │ Brand     │ SKU      │ GTIN           │ Name
Books         │ Gramedia  │ BOOK001  │ 1234567890100  │ Python for Beginners
Fashion       │ Nike      │ FASH001  │ 1234567890200  │ Air Max 90
Food          │ Nestle    │ FOOD001  │ 1234567890300  │ Nescafe Coffee 500g
```

---

## Support

If you encounter issues:
1. Check this guide first
2. Try downloading a fresh template
3. Verify data format matches examples
4. Contact system administrator if problems persist

---

**Last Updated:** December 8, 2025  
**Feature:** Header Template Download  
**Status:** Available for All Users
