<?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <span class="badge badge-primary"><?php echo e($role); ?></span>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH D:\project warnet\Nameless\Modules/User\Resources/views/users/partials/roles.blade.php ENDPATH**/ ?>