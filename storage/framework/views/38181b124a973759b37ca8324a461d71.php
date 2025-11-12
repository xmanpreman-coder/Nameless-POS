<?php $__env->startSection('title', 'Print Barcode'); ?>

<?php $__env->startPush('page_css'); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Print Barcode</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>NOTE: Barcode akan dibuat berdasarkan GTIN (jika ada) atau SKU. Pastikan SKU/GTIN adalah angka numerik untuk menghasilkan barcode!</strong>
                </div>
            </div>
            <div class="col-md-12">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('barcode.product-table', []);

$__html = app('livewire')->mount($__name, $__params, 'Pp1ANxu', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Product\Resources/views/barcode/index.blade.php ENDPATH**/ ?>