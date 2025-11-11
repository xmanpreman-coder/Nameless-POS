<?php $__env->startSection('code', '404 😵'); ?>

<?php $__env->startSection('title', __('Page Not Found')); ?>

<?php $__env->startSection('image'); ?>
    <div style="background-image: url(https://picsum.photos/seed/picsum/1920/1080);" class="absolute pin bg-no-repeat md:bg-left lg:bg-center bg-cover"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('message', __('Sorry, the page you are looking for could not be found.')); ?>

<?php echo $__env->make('errors.illustrated-layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\resources\views/errors/404.blade.php ENDPATH**/ ?>