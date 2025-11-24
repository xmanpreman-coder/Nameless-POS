<?php if($data->status == 'Pending'): ?>
    <span class="badge badge-info">
        <?php echo e($data->status); ?>

    </span>
<?php elseif($data->status == 'Shipped'): ?>
    <span class="badge badge-primary">
        <?php echo e($data->status); ?>

    </span>
<?php else: ?>
    <span class="badge badge-success">
        <?php echo e($data->status); ?>

    </span>
<?php endif; ?>
<?php /**PATH D:\project warnet\Nameless\Modules/Sale\Resources/views/partials/status.blade.php ENDPATH**/ ?>