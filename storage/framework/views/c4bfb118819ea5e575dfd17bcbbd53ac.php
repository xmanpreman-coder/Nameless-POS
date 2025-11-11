

<?php $__env->startSection('title', 'Create Product'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('products.index')); ?>">Products</a></li>
        <li class="breadcrumb-item active">Add</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <form id="product-form" action="<?php echo e(route('products.store')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php echo $__env->make('utils.alerts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <div class="form-group">
                        <button class="btn btn-primary">Create Product <i class="bi bi-check"></i></button>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_name">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" required value="<?php echo e(old('product_name')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_sku">SKU (Gudang) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_sku" name="product_sku" required value="<?php echo e(old('product_sku')); ?>" placeholder="Kode SKU untuk gudang">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_gtin">GTIN (Barcode) <i class="bi bi-question-circle-fill text-info" data-toggle="tooltip" data-placement="top" title="Global Trade Item Number, biasanya nomor barcode di produk"></i></label>
                                        <input type="text" class="form-control" id="product_gtin" name="product_gtin" value="<?php echo e(old('product_gtin')); ?>" placeholder="Kode GTIN/Barcode produk">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control" name="category_id" id="category_id" required>
                                            <option value="" selected disabled>Select Category</option>
                                            <?php $__currentLoopData = \Modules\Product\Entities\Category::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($category->id); ?>"><?php echo e($category->category_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="input-group-append d-flex">
                                            <button data-toggle="modal" data-target="#categoryCreateModal" class="btn btn-outline-primary" type="button">
                                                Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_barcode_symbology">Barcode Symbology <span class="text-danger">*</span></label>
                                        <select class="form-control" name="product_barcode_symbology" id="product_barcode_symbology" required>
                                            <option value="" selected disabled>Select Symbology</option>
                                            <option value="C128">Code 128</option>
                                            <option value="C39">Code 39</option>
                                            <option value="UPCA">UPC-A</option>
                                            <option value="UPCE">UPC-E</option>
                                            <option selected value="EAN13">EAN-13</option><option value="EAN8">EAN-8</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_cost">Cost <span class="text-danger">*</span></label>
                                        <input id="product_cost" type="text" class="form-control" name="product_cost" required value="<?php echo e(old('product_cost')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_price">Price <span class="text-danger">*</span></label>
                                        <input id="product_price" type="text" class="form-control" name="product_price" required value="<?php echo e(old('product_price')); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_quantity">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="product_quantity" name="product_quantity" required value="<?php echo e(old('product_quantity')); ?>" min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_stock_alert">Alert Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="product_stock_alert" name="product_stock_alert" required value="<?php echo e(old('product_stock_alert', 0)); ?>" min="0" max="100">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_order_tax">Tax (%)</label>
                                        <input type="number" class="form-control" id="product_order_tax" name="product_order_tax" value="<?php echo e(old('product_order_tax')); ?>" min="1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_tax_type">Tax type</label>
                                        <select class="form-control" name="product_tax_type" id="product_tax_type">
                                            <option value="" selected >Select Tax Type</option>
                                            <option value="1">Exclusive</option>
                                            <option value="2">Inclusive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="product_unit">Unit <i class="bi bi-question-circle-fill text-info" data-toggle="tooltip" data-placement="top" title="This short text will be placed after Product Quantity."></i> <span class="text-danger">*</span></label>
                                        <select class="form-control" name="product_unit" id="product_unit" required>
                                            <option value="" selected >Select Unit</option>
                                            <?php $__currentLoopData = \Modules\Setting\Entities\Unit::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($unit->short_name); ?>"><?php echo e($unit->name . ' | ' . $unit->short_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="product_note">Note</label>
                                <textarea name="product_note" id="product_note" rows="4 " class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Product Images <i class="bi bi-question-circle-fill text-info" data-toggle="tooltip" data-placement="top" title="Max Files: 3, Max File Size: 1MB, Image Size: 400x400"></i></label>
                                <div class="dropzone d-flex flex-wrap align-items-center justify-content-center" id="document-dropzone">
                                    <div class="dz-message" data-dz-message>
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Create Category Modal -->
    <?php echo $__env->make('product::includes.category-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('third_party_scripts'); ?>
    <script src="<?php echo e(asset('js/dropzone.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page_scripts'); ?>
    <script>
        var uploadedDocumentMap = {}
        Dropzone.options.documentDropzone = {
            url: '<?php echo e(route('dropzone.upload')); ?>',
            maxFilesize: 1,
            acceptedFiles: '.jpg, .jpeg, .png',
            maxFiles: 3,
            addRemoveLinks: true,
            dictRemoveFile: "<i class='bi bi-x-circle text-danger'></i> remove",
            headers: {
                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
            },
            success: function (file, response) {
                $('form').append('<input type="hidden" name="document[]" value="' + response.name + '">');
                uploadedDocumentMap[file.name] = response.name;
            },
            removedfile: function (file) {
                file.previewElement.remove();
                var name = '';
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name;
                } else {
                    name = uploadedDocumentMap[file.name];
                }
                $.ajax({
                    type: "POST",
                    url: "<?php echo e(route('dropzone.delete')); ?>",
                    data: {
                        '_token': "<?php echo e(csrf_token()); ?>",
                        'file_name': `${name}`
                    },
                });
                $('form').find('input[name="document[]"][value="' + name + '"]').remove();
            },
            init: function () {
                <?php if(isset($product) && $product->getMedia('images')): ?>
                var files = <?php echo json_encode($product->getMedia('images')); ?>;
                for (var i in files) {
                    var file = files[i];
                    this.options.addedfile.call(this, file);
                    this.options.thumbnail.call(this, file, file.original_url);
                    file.previewElement.classList.add('dz-complete');
                    $('form').append('<input type="hidden" name="document[]" value="' + file.file_name + '">');
                }
                <?php endif; ?>
            }
        }
    </script>

    <script src="<?php echo e(asset('js/jquery-mask-money.js')); ?>"></script>
    <script>
        $(document).ready(function () {
            $('#product_cost').maskMoney({
                prefix:'<?php echo e(settings()->currency->symbol); ?>',
                thousands:'<?php echo e(settings()->currency->thousand_separator); ?>',
                decimal:'<?php echo e(settings()->currency->decimal_separator); ?>',
            });
            $('#product_price').maskMoney({
                prefix:'<?php echo e(settings()->currency->symbol); ?>',
                thousands:'<?php echo e(settings()->currency->thousand_separator); ?>',
                decimal:'<?php echo e(settings()->currency->decimal_separator); ?>',
            });

            $('#product-form').submit(function () {
                var product_cost = $('#product_cost').maskMoney('unmasked')[0];
                var product_price = $('#product_price').maskMoney('unmasked')[0];
                $('#product_cost').val(product_cost);
                $('#product_price').val(product_price);
            });
        });
    </script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Product\Resources/views/products/create.blade.php ENDPATH**/ ?>