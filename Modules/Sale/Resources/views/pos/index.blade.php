@extends('layouts.app')

@section('title', 'POS')

@section('third_party_stylesheets')

@endsection

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">POS</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('utils.alerts')
            </div>
            <div class="col-lg-7">
                <livewire:search-product/>
                @include('includes.scanner-modal')
                @include('includes.scanner-help')
                <livewire:pos.product-list :categories="$product_categories"/>
            </div>
            <div class="col-lg-5">
                <livewire:pos.checkout :cart-instance="'sale'" :customers="$customers"/>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script>
        // Pass scanner settings to JavaScript
        window.scannerSettings = @json($scanner_settings);
    </script>
    <!-- QuaggaJS Library for Barcode Scanning -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <!-- External Scanner Handler -->
    <script src="{{ asset('js/external-scanner.js') }}"></script>
    <script src="{{ asset('js/pos-scanner.js') }}"></script>
    <script src="{{ asset('js/jquery-mask-money.js') }}"></script>
    <script>
        $(document).ready(function () {
            window.addEventListener('showCheckoutModal', event => {
                $('#checkoutModal').modal('show');

                $('#paid_amount').maskMoney({
                    prefix:'{{ settings()->currency->symbol }}',
                    thousands:'{{ settings()->currency->thousand_separator }}',
                    decimal:'{{ settings()->currency->decimal_separator }}',
                    allowZero: false,
                });

                $('#total_amount').maskMoney({
                    prefix:'{{ settings()->currency->symbol }}',
                    thousands:'{{ settings()->currency->thousand_separator }}',
                    decimal:'{{ settings()->currency->decimal_separator }}',
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

@endpush
