<div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div>
                <!-- __BLOCK__ --><?php if(session()->has('message')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <div class="alert-body">
                            <span><?php echo e(session('message')); ?></span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?> <!-- __ENDBLOCK__ -->

                <div class="form-group">
                    <label for="customer_id">Customer <small class="text-muted">(Opsional - untuk customer tetap)</small></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <a href="<?php echo e(route('customers.create')); ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i>
                            </a>
                        </div>
                        <select wire:model.live="customer_id" id="customer_id" class="form-control">
                            <option value="">Walk-in Customer (Random)</option>
                            <!-- __BLOCK__ --><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->customer_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <!-- __ENDBLOCK__ -->
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr class="text-center">
                            <th class="align-middle">Product</th>
                            <th class="align-middle">Price</th>
                            <th class="align-middle">Quantity</th>
                            <th class="align-middle">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- __BLOCK__ --><?php if($cart_items->isNotEmpty()): ?>
                            <!-- __BLOCK__ --><?php $__currentLoopData = $cart_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cart_item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="align-middle">
                                        <?php echo e($cart_item->name); ?> <br>
                                        <span class="badge badge-success">
                                        <?php echo e($cart_item->options->code); ?>

                                    </span>
                                        <?php echo $__env->make('livewire.includes.product-cart-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    </td>

                                    <td class="align-middle">
                                        <?php echo e(format_currency($cart_item->price)); ?>

                                    </td>

                                    <td class="align-middle">
                                        <?php echo $__env->make('livewire.includes.product-cart-quantity', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    </td>

                                    <td class="align-middle text-center">
                                        <a href="#" wire:click.prevent="removeItem('<?php echo e($cart_item->rowId); ?>')">
                                            <i class="bi bi-x-circle font-2xl text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <!-- __ENDBLOCK__ -->
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">
                        <span class="text-danger">
                            Please search & select products!
                        </span>
                                </td>
                            </tr>
                        <?php endif; ?> <!-- __ENDBLOCK__ -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th>Order Tax (<?php echo e($global_tax); ?>%)</th>
                                <td>(+) <?php echo e(format_currency(Cart::instance($cart_instance)->tax())); ?></td>
                            </tr>
                            <tr>
                                <th>Discount (<?php echo e($global_discount); ?>%)</th>
                                <td>(-) <?php echo e(format_currency(Cart::instance($cart_instance)->discount())); ?></td>
                            </tr>
                            <tr>
                                <th>Shipping</th>
                                <input type="hidden" value="<?php echo e($shipping); ?>" name="shipping_amount">
                                <td>(+) <?php echo e(format_currency($shipping)); ?></td>
                            </tr>
                            <tr class="text-primary">
                                <th>Grand Total</th>
                                <?php
                                    $total_with_shipping = Cart::instance($cart_instance)->total() + (float) $shipping
                                ?>
                                <th>
                                    (=) <?php echo e(format_currency($total_with_shipping)); ?>

                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="tax_percentage">Order Tax (%)</label>
                        <input wire:model.blur="global_tax" type="number" class="form-control" min="0" max="100" value="<?php echo e($global_tax); ?>" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="discount_percentage">Discount (%)</label>
                        <input wire:model.blur="global_discount" type="number" class="form-control" min="0" max="100" value="<?php echo e($global_discount); ?>" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="shipping_amount">Shipping</label>
                        <input wire:model.blur="shipping" type="number" class="form-control" min="0" value="0" required step="0.01">
                    </div>
                </div>
            </div>

            <div class="form-group d-flex justify-content-center flex-wrap mb-0">
                <button wire:click="resetCart" type="button" class="btn btn-pill btn-danger mr-3"><i class="bi bi-x"></i> Reset</button>
                <button wire:loading.attr="disabled" wire:click="proceed" type="button" class="btn btn-pill btn-primary" <?php echo e($total_amount == 0 ? 'disabled' : ''); ?>><i class="bi bi-check"></i> Proceed</button>
            </div>
        </div>
    </div>

    
    <?php echo $__env->make('livewire.pos.includes.checkout-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</div>

<?php /**PATH D:\project warnet\Nameless\resources\views/livewire/pos/checkout.blade.php ENDPATH**/ ?>