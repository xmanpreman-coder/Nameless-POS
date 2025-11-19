<div class="d-inline-block">
    <!-- Button trigger Discount Modal -->
    <span wire:click="$dispatch('discountModalRefresh', { product_id: <?php echo e($cart_item->id); ?>, row_id: '<?php echo e($cart_item->rowId); ?>' })" role="button" class="badge badge-warning pointer-event" data-toggle="modal" data-target="#discountModal<?php echo e($cart_item->id); ?>">
        <i class="bi bi-pencil-square text-white"></i>
    </span>
    <!-- Discount Modal -->
    <div wire:ignore.self class="modal fade" id="discountModal<?php echo e($cart_item->id); ?>" tabindex="-1" role="dialog" aria-labelledby="discountModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discountModalLabel">
                        <?php echo e($cart_item->name); ?>

                        <br>
                        <span class="badge badge-success">
                        <?php echo e($cart_item->options->code); ?>

                    </span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- __BLOCK__ --><?php if(session()->has('discount_message' . $cart_item->id)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="alert-body">
                                <span><?php echo e(session('discount_message' . $cart_item->id)); ?></span>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?> <!-- __ENDBLOCK__ -->
                    <div class="form-group">
                        <label>Discount Type <span class="text-danger">*</span></label>
                        <select wire:model.live="discount_type.<?php echo e($cart_item->id); ?>" class="form-control" required>
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <!-- __BLOCK__ --><?php if($discount_type[$cart_item->id] == 'percentage'): ?>
                            <label>Discount(%) <span class="text-danger">*</span></label>
                            <input wire:model="item_discount.<?php echo e($cart_item->id); ?>" type="number" class="form-control" value="<?php echo e($item_discount[$cart_item->id]); ?>" min="0" max="100">
                        <?php elseif($discount_type[$cart_item->id] == 'fixed'): ?>
                            <label>Discount <span class="text-danger">*</span></label>
                            <input wire:model="item_discount.<?php echo e($cart_item->id); ?>" type="number" class="form-control" value="<?php echo e($item_discount[$cart_item->id]); ?>">
                        <?php endif; ?> <!-- __ENDBLOCK__ -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button wire:click="setProductDiscount('<?php echo e($cart_item->rowId); ?>', <?php echo e($cart_item->id); ?>)" type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\project warnet\Nameless\resources\views/livewire/includes/product-cart-modal.blade.php ENDPATH**/ ?>