

<?php $__env->startSection('title', 'POS'); ?>

<?php $__env->startSection('third_party_stylesheets'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item active">POS</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php echo $__env->make('utils.alerts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            <div class="col-lg-7">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('search-product', []);

$__html = app('livewire')->mount($__name, $__params, 'iGfI2UL', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                <?php echo $__env->make('includes.scanner-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php echo $__env->make('includes.scanner-help', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pos.product-list', ['categories' => $product_categories]);

$__html = app('livewire')->mount($__name, $__params, 'tALr8zq', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
            <div class="col-lg-5">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('pos.checkout', ['cartInstance' => 'sale','customers' => $customers]);

$__html = app('livewire')->mount($__name, $__params, '7SmqzP6', $__slots ?? [], get_defined_vars());

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

<?php $__env->startPush('page_scripts'); ?>
    <!-- QuaggaJS Library for Barcode Scanning -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="<?php echo e(asset('js/pos-scanner.js')); ?>"></script>
    <script src="<?php echo e(asset('js/jquery-mask-money.js')); ?>"></script>
    <script>
        $(document).ready(function () {
            window.addEventListener('showCheckoutModal', event => {
                $('#checkoutModal').modal('show');

                $('#paid_amount').maskMoney({
                    prefix:'<?php echo e(settings()->currency->symbol); ?>',
                    thousands:'<?php echo e(settings()->currency->thousand_separator); ?>',
                    decimal:'<?php echo e(settings()->currency->decimal_separator); ?>',
                    allowZero: false,
                });

                $('#total_amount').maskMoney({
                    prefix:'<?php echo e(settings()->currency->symbol); ?>',
                    thousands:'<?php echo e(settings()->currency->thousand_separator); ?>',
                    decimal:'<?php echo e(settings()->currency->decimal_separator); ?>',
                    allowZero: true,
                });

                $('#paid_amount').maskMoney('mask');
                $('#total_amount').maskMoney('mask');

                $('#checkout-form').submit(function (e) {
                    e.preventDefault();
                    
                    var paid_amount = $('#paid_amount').maskMoney('unmasked')[0];
                    $('#paid_amount').val(paid_amount);
                    var total_amount = $('#total_amount').maskMoney('unmasked')[0];
                    $('#total_amount').val(total_amount);
                    
                    // Show loading
                    $('#checkout-content').hide();
                    $('#checkout-loading').show();
                    $('#submit-checkout-btn').prop('disabled', true);
                    
                    // Submit via AJAX
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Close modal first
                                $('#checkoutModal').modal('hide');
                                
                                // Reset form
                                $('#checkout-form')[0].reset();
                                
                                // Show success message
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(response.message || 'POS Sale Created!');
                                }
                                
                                // Print nota terlebih dahulu
                                openPrintWindow('sales', response.sale_id);
                                
                                // Reload halaman setelah 1.5 detik untuk reset cart dan semua state
                                // Ini memastikan cart di-reset dengan benar
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            }
                        },
                        error: function(xhr) {
                            // Show error message
                            var errorMsg = 'An error occurred. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                            
                            // Hide loading, show content
                            $('#checkout-loading').hide();
                            $('#checkout-content').show();
                            $('#submit-checkout-btn').prop('disabled', false);
                        }
                    });
                    
                    return false;
                });
            });
        });
        
        // Function untuk print window (sama seperti di sales/index.blade.php)
        let printWindowOpen = false;
        
        function openPrintWindow(type, id) {
            // Prevent multiple print windows
            if (printWindowOpen) {
                return;
            }
            
            const url = type === 'sales' 
                ? '/sales/pos/print/' + id
                : type === 'purchases'
                ? '/purchases/print/' + id
                : '/quotations/print/' + id;
            
            const printWindow = window.open(url, '_blank', 'width=500,height=700');
            if (printWindow) {
                printWindowOpen = true;
                
                // Reset flag setelah window ditutup
                const checkClosed = setInterval(function() {
                    if (printWindow.closed) {
                        printWindowOpen = false;
                        clearInterval(checkClosed);
                    }
                }, 500);
            }
        }
    </script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Sale\Resources/views/pos/index.blade.php ENDPATH**/ ?>