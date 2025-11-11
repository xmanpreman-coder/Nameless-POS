<a href="<?php echo e(route('users.edit', $data->id)); ?>" class="btn btn-info btn-sm">
    <i class="bi bi-pencil"></i>
</a>
<button id="delete" class="btn btn-danger btn-sm" onclick="
    event.preventDefault();
    if (confirm('Are you sure? It will delete the data permanently!')) {
    document.getElementById('destroy<?php echo e($data->id); ?>').submit();
    }
    ">
    <i class="bi bi-trash"></i>
    <form id="destroy<?php echo e($data->id); ?>" class="d-none" action="<?php echo e(route('users.destroy', $data->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('delete'); ?>
    </form>
</button>
<?php /**PATH D:\project warnet\Nameless\Modules/User\Resources/views/users/partials/actions.blade.php ENDPATH**/ ?>