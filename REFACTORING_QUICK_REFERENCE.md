# 🚀 QUICK REFERENCE: product_code → product_sku REFACTORING

## What Changed?

### Database
```
✅ BEFORE: product_code (primary SKU field)
✅ AFTER:  product_sku (new primary SKU field)
✅ ADDED:  product_gtin (GTIN/barcode field)

Tabel yang terpengaruh:
- products (primary)
- sale_details (removed product_code)
- purchase_details (removed product_code)
- quotation_details (removed product_code)
- sale_return_details (removed product_code)
- purchase_return_details (removed product_code)
```

### Code
```
✅ Livewire Components:
  - SearchProduct.php → uses product_sku
  - Checkout.php → uses product_sku
  - ProductCart.php → uses product_sku
  - Barcode/ProductTable.php → uses product_sku

✅ Seeders:
  - DummyDataSeeder.php → updated
  - DatabaseSeeder.php → updated

✅ Migrations:
  - All detail tables updated
  - product_code removed from products
```

## Testing

Run verification:
```bash
php verify_refactoring.php
```

Expected output:
```
✅ Product model has product_sku
✅ Database columns updated to product_sku
✅ Search uses product_sku
✅ Migrations updated

🎉 REFACTORING SUCCESSFUL - Ready for production!
```

## Migration Steps

If needing to re-run:
```bash
# Full reset (development only)
php artisan migrate:fresh --seed

# Or just apply pending migrations
php artisan migrate
```

## Key Files

### Migrations Added
- `database/migrations/2025_11_19_000001_refactor_product_code_to_sku.php`
- `database/migrations/2025_11_19_000002_remove_product_code_from_products.php`

### Modified Files (39 total)
- 4 Livewire components
- 5 migration files (detail tables)
- 2 seeder files
- Documentation

## Production Deployment

1. Backup database:
   ```bash
   cp database/database.sqlite database/database.sqlite.backup.$(date +%Y%m%d)
   ```

2. Deploy:
   ```bash
   php artisan migrate
   php artisan cache:clear
   ```

3. Verify:
   ```bash
   php verify_refactoring.php
   ```

## Rollback (if needed)

```bash
php artisan migrate:rollback --step=2
```

This will revert both refactoring migrations.

## API Changes

### Product Response (API)

**Before:**
```json
{
  "id": 1,
  "product_code": "PRD001",
  "product_name": "Laptop"
}
```

**After:**
```json
{
  "id": 1,
  "product_sku": "PRD001",
  "product_gtin": null,
  "product_name": "Laptop"
}
```

Update any external integrations accordingly.

## Search/Filter Impact

### Livewire Search
```php
// Old - NO LONGER WORKS
Product::where('product_code', 'PRD001')

// New - USE THIS
Product::where('product_sku', 'PRD001')
```

### Database Queries
```php
// Old query
SaleDetail::where('product_code', 'PRD001')

// New query
SaleDetail::where('product_sku', 'PRD001')
```

## GTIN Field Usage

New `product_gtin` field available for:
- Barcode storage (EAN-13, UPC, etc.)
- Alternative product identifier
- Optional field (nullable)

Can be populated via:
1. CSV import
2. Manual entry in product form
3. Barcode scanner integration
4. API

## FAQ

**Q: Will existing product data be lost?**  
A: No, all SKU data migrated from product_code to product_sku

**Q: Can I revert this?**  
A: Yes, `php artisan migrate:rollback --step=2` will revert

**Q: Do I need to update anything?**  
A: If using APIs or custom code, update references from product_code to product_sku

**Q: What about old reports?**  
A: Old transaction records (sales_details, etc.) use product_sku now, no issues

---

**Status:** ✅ Complete  
**Date:** Nov 19, 2025  
**Tested:** ✅ Yes  
**Production Ready:** ✅ Yes
