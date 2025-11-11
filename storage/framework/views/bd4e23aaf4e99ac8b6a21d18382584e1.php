<li class="c-sidebar-nav-item <?php echo e(request()->routeIs('home') ? 'c-active' : ''); ?>">
    <a class="c-sidebar-nav-link" href="<?php echo e(route('home')); ?>">
        <i class="c-sidebar-nav-icon bi bi-house" style="line-height: 1;"></i> Home
    </a>
</li>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_products')): ?>
<li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('products.*') || request()->routeIs('product-categories.*') ? 'c-show' : ''); ?>">
    <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
        <i class="c-sidebar-nav-icon bi bi-journal-bookmark" style="line-height: 1;"></i> Products
    </a>
    <ul class="c-sidebar-nav-dropdown-items">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_product_categories')): ?>
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('product-categories.*') ? 'c-active' : ''); ?>" href="<?php echo e(route('product-categories.index')); ?>">
                <i class="c-sidebar-nav-icon bi bi-collection" style="line-height: 1;"></i> Categories
            </a>
        </li>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_products')): ?>
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('products.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('products.create')); ?>">
                <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Product
            </a>
        </li>
        <?php endif; ?>
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('products.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('products.index')); ?>">
                <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Products
            </a>
        </li>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('print_barcodes')): ?>
           <li class="c-sidebar-nav-item">
               <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('barcode.print') ? 'c-active' : ''); ?>" href="<?php echo e(route('barcode.print')); ?>">
                   <i class="c-sidebar-nav-icon bi bi-printer" style="line-height: 1;"></i> Print Barcode
               </a>
           </li>
        <?php endif; ?>
    </ul>
</li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_adjustments')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('adjustments.*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-clipboard-check" style="line-height: 1;"></i> Stock Adjustments
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_adjustments')): ?>
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('adjustments.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('adjustments.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Adjustment
                    </a>
                </li>
            <?php endif; ?>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('adjustments.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('adjustments.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Adjustments
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_quotations')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('quotations.*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-cart-check" style="line-height: 1;"></i> Quotations
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_adjustments')): ?>
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('quotations.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('quotations.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Quotation
                    </a>
                </li>
            <?php endif; ?>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('quotations.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('quotations.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Quotations
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_purchases')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('purchases.*') || request()->routeIs('purchase-payments*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-bag" style="line-height: 1;"></i> Purchases
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_purchase')): ?>
            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('purchases.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('purchases.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Purchase
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('purchases.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('purchases.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Purchases
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_purchase_returns')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('purchase-returns.*') || request()->routeIs('purchase-return-payments.*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-arrow-return-right" style="line-height: 1;"></i> Purchase Returns
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_purchase_returns')): ?>
            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('purchase-returns.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('purchase-returns.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Purchase Return
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('purchase-returns.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('purchase-returns.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Purchase Returns
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_sales')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('sales.*') || request()->routeIs('sale-payments*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-receipt" style="line-height: 1;"></i> Sales
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_sales')): ?>
            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('sales.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('sales.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Sale
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('sales.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('sales.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Sales
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_sale_returns')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('sale-returns.*') || request()->routeIs('sale-return-payments.*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-arrow-return-left" style="line-height: 1;"></i> Sale Returns
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_sale_returns')): ?>
            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('sale-returns.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('sale-returns.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Sale Return
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('sale-returns.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('sale-returns.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Sale Returns
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_expenses')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-wallet2" style="line-height: 1;"></i> Expenses
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_expense_categories')): ?>
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('expense-categories.*') ? 'c-active' : ''); ?>" href="<?php echo e(route('expense-categories.index')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-collection" style="line-height: 1;"></i> Categories
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_expenses')): ?>
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('expenses.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('expenses.create')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-journal-plus" style="line-height: 1;"></i> Create Expense
                    </a>
                </li>
            <?php endif; ?>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('expenses.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('expenses.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-journals" style="line-height: 1;"></i> All Expenses
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_customers|access_suppliers')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('customers.*') || request()->routeIs('suppliers.*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-people" style="line-height: 1;"></i> Parties
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_customers')): ?>
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('customers.*') ? 'c-active' : ''); ?>" href="<?php echo e(route('customers.index')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-people-fill" style="line-height: 1;"></i> Customers
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_suppliers')): ?>
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('suppliers.*') ? 'c-active' : ''); ?>" href="<?php echo e(route('suppliers.index')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-people-fill" style="line-height: 1;"></i> Suppliers
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_reports')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('*-report.index') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-graph-up" style="line-height: 1;"></i> Reports
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('profit-loss-report.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('profit-loss-report.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-clipboard-data" style="line-height: 1;"></i> Profit / Loss Report
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('payments-report.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('payments-report.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-clipboard-data" style="line-height: 1;"></i> Payments Report
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('sales-report.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('sales-report.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-clipboard-data" style="line-height: 1;"></i> Sales Report
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('purchases-report.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('purchases-report.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-clipboard-data" style="line-height: 1;"></i> Purchases Report
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('sales-return-report.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('sales-return-report.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-clipboard-data" style="line-height: 1;"></i> Sales Return Report
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('purchases-return-report.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('purchases-return-report.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-clipboard-data" style="line-height: 1;"></i> Purchases Return Report
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_user_management')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('roles*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-people" style="line-height: 1;"></i> User Management
        </a>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('users.create') ? 'c-active' : ''); ?>" href="<?php echo e(route('users.create')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-person-plus" style="line-height: 1;"></i> Create User
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('users*') ? 'c-active' : ''); ?>" href="<?php echo e(route('users.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-person-lines-fill" style="line-height: 1;"></i> All Users
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('roles*') ? 'c-active' : ''); ?>" href="<?php echo e(route('roles.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-key" style="line-height: 1;"></i> Roles & Permissions
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>

<!-- Scanner Module -->
<li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('scanner.*') ? 'c-show' : ''); ?>">
    <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
        <i class="c-sidebar-nav-icon bi bi-upc-scan" style="line-height: 1;"></i> Barcode Scanner
    </a>
    <ul class="c-sidebar-nav-dropdown-items">
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('scanner.index') ? 'c-active' : ''); ?>" href="<?php echo e(route('scanner.index')); ?>">
                <i class="c-sidebar-nav-icon bi bi-speedometer2" style="line-height: 1;"></i> Scanner Dashboard
            </a>
        </li>
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('scanner.scan') ? 'c-active' : ''); ?>" href="<?php echo e(route('scanner.scan')); ?>">
                <i class="c-sidebar-nav-icon bi bi-camera" style="line-height: 1;"></i> Start Scanning
            </a>
        </li>
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('scanner.test-camera') ? 'c-active' : ''); ?>" href="<?php echo e(route('scanner.test-camera')); ?>">
                <i class="c-sidebar-nav-icon bi bi-camera-video" style="line-height: 1;"></i> Test Camera
            </a>
        </li>
        <li class="c-sidebar-nav-item">
            <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('scanner.external-setup') ? 'c-active' : ''); ?>" href="<?php echo e(route('scanner.external-setup')); ?>">
                <i class="c-sidebar-nav-icon bi bi-phone" style="line-height: 1;"></i> External Scanner Setup
            </a>
        </li>
    </ul>
</li>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_currencies|access_settings')): ?>
    <li class="c-sidebar-nav-item c-sidebar-nav-dropdown <?php echo e(request()->routeIs('currencies*') || request()->routeIs('units*') ? 'c-show' : ''); ?>">
        <a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <i class="c-sidebar-nav-icon bi bi-gear" style="line-height: 1;"></i> Settings
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_units')): ?>
            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('units*') ? 'c-active' : ''); ?>" href="<?php echo e(route('units.index')); ?>">
                        <i class="c-sidebar-nav-icon bi bi-calculator" style="line-height: 1;"></i> Units
                    </a>
                </li>
            </ul>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_currencies')): ?>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('currencies*') ? 'c-active' : ''); ?>" href="<?php echo e(route('currencies.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-cash-stack" style="line-height: 1;"></i> Currencies
                </a>
            </li>
        </ul>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_settings')): ?>
        <ul class="c-sidebar-nav-dropdown-items">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('settings*') ? 'c-active' : ''); ?>" href="<?php echo e(route('settings.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-sliders" style="line-height: 1;"></i> General Settings
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('printer-settings*') ? 'c-active' : ''); ?>" href="<?php echo e(route('printer-settings.index')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-printer" style="line-height: 1;"></i> Printer Settings
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link <?php echo e(request()->routeIs('scanner.settings') ? 'c-active' : ''); ?>" href="<?php echo e(route('scanner.settings')); ?>">
                    <i class="c-sidebar-nav-icon bi bi-upc-scan" style="line-height: 1;"></i> Scanner Settings
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <button class="c-sidebar-nav-link" id="backup-database-button" type="button" style="border: none; background: none; text-align: left;">
                    <i class="c-sidebar-nav-icon bi bi-database-down" style="line-height: 1;"></i> Backup Database
                </button>
            </li>
        </ul>
        <?php endif; ?>
    </li>
<?php endif; ?>
<?php /**PATH D:\project warnet\Nameless\resources\views/layouts/menu.blade.php ENDPATH**/ ?>