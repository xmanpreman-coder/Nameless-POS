<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_products')): ?>
<a href="<?php echo e(route('products.edit', $data->id)); ?>" class="btn btn-info btn-sm">
    <i class="bi bi-pencil"></i>
</a>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show_products')): ?>
<a href="<?php echo e(route('products.show', $data->id)); ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-eye"></i>
</a>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_products')): ?>
<button id="delete" class="btn btn-danger btn-sm" onclick="
    event.preventDefault();
    if (confirm('Are you sure? It will delete the data permanently!')) {
        document.getElementById('destroy<?php echo e($data->id); ?>').submit()
    }
    ">
    <i class="bi bi-trash"></i>
    <form id="destroy<?php echo e($data->id); ?>" class="d-none" action="<?php echo e(route('products.destroy', $data->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('delete'); ?>
    </form>
</button>
<?php endif; ?>
<?php /**PATH D:\project warnet\Nameless\Modules/Product\Resources/views/products/partials/actions.blade.php ENDPATH**/ ?>