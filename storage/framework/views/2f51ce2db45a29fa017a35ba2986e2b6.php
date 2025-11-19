<div class="input-group d-flex justify-content-center">
    <input wire:model="quantity.<?php echo e($cart_item->id); ?>" style="min-width: 40px;max-width: 90px;" type="number" class="form-control" value="<?php echo e($cart_item->qty); ?>" min="1">
    <div class="input-group-append">
        <button type="button" wire:click="updateQuantity('<?php echo e($cart_item->rowId); ?>', <?php echo e($cart_item->id); ?>)" class="btn btn-info">
            <i class="bi bi-check"></i>
        </button>
    </div>
</div>
<?php /**PATH D:\project warnet\Nameless\resources\views/livewire/includes/product-cart-quantity.blade.php ENDPATH**/ ?>