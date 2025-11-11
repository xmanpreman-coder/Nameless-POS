<div class="position-relative">
    <div class="card mb-0 border-0 shadow-sm">
        <div class="card-body">
            <div class="form-group mb-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="bi bi-search text-primary"></i>
                        </div>
                    </div>
                    <input wire:keydown.escape="resetQuery" wire:model.live.debounce.500ms="query" type="text" class="form-control" placeholder="Type product name or code....">
                </div>
            </div>
        </div>
    </div>

    <div wire:loading class="card position-absolute mt-1 border-0" style="z-index: 1;left: 0;right: 0;">
        <div class="card-body shadow">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- __BLOCK__ --><?php if(!empty($query)): ?>
        <div wire:click="resetQuery" class="position-fixed w-100 h-100" style="left: 0; top: 0; right: 0; bottom: 0;z-index: 1;"></div>
        <!-- __BLOCK__ --><?php if($search_results->isNotEmpty()): ?>
            <div class="card position-absolute mt-1" style="z-index: 2;left: 0;right: 0;border: 0;">
                <div class="card-body shadow">
                    <ul class="list-group list-group-flush">
                        <!-- __BLOCK__ --><?php $__currentLoopData = $search_results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="list-group-item list-group-item-action">
                                <a wire:click="resetQuery" wire:click.prevent="selectProduct(<?php echo e($result); ?>)" href="#">
                                    <?php echo e($result->product_name); ?> | SKU: <?php echo e($result->product_sku ?? $result->product_code ?? 'N/A'); ?><!-- __BLOCK__ --><?php if($result->product_gtin): ?> | GTIN: <?php echo e($result->product_gtin); ?><?php endif; ?> <!-- __ENDBLOCK__ -->
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($search_results->count() >= $how_many): ?>
                             <li class="list-group-item list-group-item-action text-center">
                                 <a wire:click.prevent="loadMore" class="btn btn-primary btn-sm" href="#">
                                     Load More <i class="bi bi-arrow-down-circle"></i>
                                 </a>
                             </li>
                        <?php endif; ?> <!-- __ENDBLOCK__ -->
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="card position-absolute mt-1 border-0" style="z-index: 1;left: 0;right: 0;">
                <div class="card-body shadow">
                    <div class="alert alert-warning mb-0">
                        No Product Found....
                    </div>
                </div>
            </div>
        <?php endif; ?> <!-- __ENDBLOCK__ -->
    <?php endif; ?> <!-- __ENDBLOCK__ -->
</div>
<?php /**PATH D:\project warnet\Nameless\resources\views/livewire/search-product.blade.php ENDPATH**/ ?>