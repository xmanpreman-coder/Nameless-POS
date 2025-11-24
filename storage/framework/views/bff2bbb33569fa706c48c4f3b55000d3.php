<?php $__env->startSection('title', 'Product Details'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('products.index')); ?>">Products</a></li>
        <li class="breadcrumb-item active">Details</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid mb-4">
        <div class="row mb-3">
            <div class="col-md-12">
                    <?php
                    $barcodeValue = $product->product_gtin ?? $product->product_sku ?? '';
                ?>
                <?php if($barcodeValue): ?>
                    <?php echo \Milon\Barcode\Facades\DNS1DFacade::getBarCodeSVG($barcodeValue, $product->product_barcode_symbology, 2, 110); ?>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-9">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <tr>
                                    <th>SKU (Gudang)</th>
                                    <td><?php echo e($product->product_sku ?? 'N/A'); ?></td>
                                </tr>
                                <?php if($product->product_gtin): ?>
                                <tr>
                                    <th>GTIN (Barcode)</th>
                                    <td><?php echo e($product->product_gtin); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Barcode Symbology</th>
                                    <td><?php echo e($product->product_barcode_symbology); ?></td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td><?php echo e($product->product_name); ?></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td><?php echo e($product->category->category_name); ?></td>
                                </tr>
                                <tr>
                                    <th>Cost</th>
                                    <td><?php echo e(format_currency($product->product_cost)); ?></td>
                                </tr>
                                <tr>
                                    <th>Price</th>
                                    <td><?php echo e(format_currency($product->product_price)); ?></td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td><?php echo e($product->product_quantity . ' ' . $product->product_unit); ?></td>
                                </tr>
                                <tr>
                                    <th>Stock Worth</th>
                                    <td>
                                        COST:: <?php echo e(format_currency($product->product_cost * $product->product_quantity)); ?> /
                                        PRICE:: <?php echo e(format_currency($product->product_price * $product->product_quantity)); ?>

                                    </td>
                                </tr>
                                <tr>
                                    <th>Alert Quantity</th>
                                    <td><?php echo e($product->product_stock_alert); ?></td>
                                </tr>
                                <tr>
                                    <th>Tax (%)</th>
                                    <td><?php echo e($product->product_order_tax ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Tax Type</th>
                                    <td>
                                        <?php if($product->product_tax_type == 1): ?>
                                            Exclusive
                                        <?php elseif($product->product_tax_type == 2): ?>
                                            Inclusive
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Note</th>
                                    <td><?php echo e($product->product_note ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $product->getMedia('images'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <img src="<?php echo e($media->getUrl()); ?>" alt="Product Image" class="img-fluid img-thumbnail mb-2">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <img src="<?php echo e($product->getFirstMediaUrl('images')); ?>" alt="Product Image" class="img-fluid img-thumbnail mb-2">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>




<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Product\Resources/views/products/show.blade.php ENDPATH**/ ?>