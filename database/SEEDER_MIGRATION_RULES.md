# Database Seeder & Migration Rules

## ⚠️ CRITICAL RULES - Read Before Any Database Operation

### Rule 1: CREATE IF NOT EXISTS
- **When creating seeders or migrations**: Only create if the file does **NOT** already exist
- **Check first**: Always verify the file is new before creating
- **Purpose**: Prevent overwriting existing logic and data configurations

### Rule 2: NEVER DROP - ONLY EDIT OR ADD
- **FORBIDDEN**: `migrate:fresh`, `migrate:rollback` on production/populated databases
- **ALLOWED**: 
  - Edit existing migration files (add new columns, indexes, constraints)
  - Add new migration files for new features
  - Edit seeder logic to add/modify seed data
- **When adding data**: Use `insertOrIgnore()` or `updateOrCreate()` to prevent duplicates
- **When modifying structure**: Create a NEW migration file, don't modify old ones

### Rule 3: Backup Before Any Migration
```bash
# Always backup before running migrations
cp database/database.sqlite database/database.sqlite.backup-$(date +%s).bak

# Run pending migrations ONLY (never fresh)
php artisan migrate
```

### Rule 4: Seeder Guidelines
- **Purpose**: Initialize reference data (settings, currencies, roles, permissions, units)
- **DO NOT seed**: Sales transactions, customer data, or production records
- **Use idempotency**: Seeders should be safe to run multiple times
- **Example**:
  ```php
  // GOOD - Will not duplicate
  DB::table('currencies')->insertOrIgnore([
      'code' => 'IDR',
      'name' => 'Indonesian Rupiah',
  ]);
  
  // BAD - Will fail if exists
  DB::table('currencies')->insert([
      'code' => 'IDR',
      'name' => 'Indonesian Rupiah',
  ]);
  ```

### Rule 5: Migration Guidelines
- **File naming**: `YYYY_MM_DD_HHMMSS_descriptive_name.php`
- **DO NOT modify**: Old migration files (they may have already run)
- **DO create new**: If you need schema changes after a migration has run
- **Always use**: `Schema::hasTable()`, `Schema::hasColumn()` to check before modifying

### Rule 6: Disaster Prevention
| Action | ✅ ALLOWED | ❌ FORBIDDEN |
|--------|-----------|------------|
| `php artisan migrate` | YES | Add pending migrations only |
| `php artisan migrate:fresh` | ONLY in dev/testing with NO data | Production, backups |
| `php artisan migrate:rollback` | ONLY in dev/testing | Production |
| `php artisan db:seed` | YES | Run seeders anytime (idempotent) |
| Edit existing migration | NO | After it's been run |
| Create new migration | YES | For schema changes |
| Direct DB queries | BE CAREFUL | Test in dev first |

---

## Examples

### ✅ CORRECT: Adding a new field
```php
// Create NEW migration file: 2025_11_22_140000_add_phone_to_users.php
Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'phone')) {
        $table->string('phone')->nullable()->after('email');
    }
});
```

### ❌ WRONG: Modifying old migration
```php
// DO NOT edit: 2025_11_11_000000_create_users_table.php
// Even if it's already been run!
```

### ✅ CORRECT: Safe seeding
```php
// Seeders/PermissionsSeeder.php
public function run()
{
    Permission::firstOrCreate(['name' => 'view_dashboard']);
    Permission::firstOrCreate(['name' => 'create_sales']);
}
```

### ❌ WRONG: Unsafe seeding
```php
// This will fail if permission exists
Permission::create(['name' => 'view_dashboard']);
```

---

## When Data Loss Happens (Recovery)

1. **Check backups folder**: `database/backups/` or `database/*.bak`
2. **Latest backup file**: `database.sqlite.TIMESTAMP.bak`
3. **Restore**:
   ```bash
   cp database/database.sqlite.20251119_154409.bak database/database.sqlite
   ```
4. **Verify**: Open the app and check if data is back

---

## Quick Checklist Before Running Migrations

- [ ] Backup created (`database.sqlite.backup.bak`)
- [ ] Using `php artisan migrate` (NOT `migrate:fresh`)
- [ ] New migration files are for new features only
- [ ] Existing migrations are NOT edited
- [ ] Seeders use `insertOrIgnore()` or `firstOrCreate()`
- [ ] Tested in dev environment first

---

**Last Updated**: 2025-11-22  
**Created by**: AI Agent (after data loss incident)  
**Never forget**: NEVER use `migrate:fresh` on production data! 🚫
