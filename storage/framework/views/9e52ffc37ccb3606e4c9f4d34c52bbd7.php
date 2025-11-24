<div class="btn-group dropleft">
    <button type="button" class="btn btn-ghost-primary dropdown rounded" data-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
    <div class="dropdown-menu">
        <a href="javascript:void(0)" onclick="printSaleNota(<?php echo e($data->id); ?>)" class="dropdown-item">
            <i class="bi bi-printer mr-2 text-primary" style="line-height: 1;"></i> Print Nota
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_sale_payments')): ?>
            <a href="<?php echo e(route('sale-payments.index', $data->id)); ?>" class="dropdown-item">
                <i class="bi bi-cash-coin mr-2 text-warning" style="line-height: 1;"></i> Show Payments
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_sale_payments')): ?>
            <?php if($data->due_amount > 0): ?>
            <a href="<?php echo e(route('sale-payments.create', $data->id)); ?>" class="dropdown-item">
                <i class="bi bi-plus-circle-dotted mr-2 text-success" style="line-height: 1;"></i> Add Payment
            </a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_sales')): ?>
            <a href="<?php echo e(route('sales.edit', $data->id)); ?>" class="dropdown-item">
                <i class="bi bi-pencil mr-2 text-primary" style="line-height: 1;"></i> Edit
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show_sales')): ?>
            <a href="<?php echo e(route('sales.show', $data->id)); ?>" class="dropdown-item">
                <i class="bi bi-eye mr-2 text-info" style="line-height: 1;"></i> Details
            </a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_sales')): ?>
            <button id="delete" class="dropdown-item" onclick="
                event.preventDefault();
                if (confirm('Are you sure? It will delete the data permanently!')) {
                document.getElementById('destroy<?php echo e($data->id); ?>').submit()
                }">
                <i class="bi bi-trash mr-2 text-danger" style="line-height: 1;"></i> Delete
                <form id="destroy<?php echo e($data->id); ?>" class="d-none" action="<?php echo e(route('sales.destroy', $data->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('delete'); ?>
                </form>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH D:\project warnet\Nameless\Modules/Sale\Resources/views/partials/actions.blade.php ENDPATH**/ ?>