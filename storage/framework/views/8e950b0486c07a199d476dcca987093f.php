<?php if($data->payment_status == 'Partial'): ?>
    <span class="badge badge-warning">
        <?php echo e($data->payment_status); ?>

    </span>
<?php elseif($data->payment_status == 'Paid'): ?>
    <span class="badge badge-success">
        <?php echo e($data->payment_status); ?>

    </span>
<?php else: ?>
    <span class="badge badge-danger">
        <?php echo e($data->payment_status); ?>

    </span>
<?php endif; ?>
<?php /**PATH D:\project warnet\Nameless\Modules/Sale\Resources/views/partials/payment-status.blade.php ENDPATH**/ ?>