

<?php $__env->startSection('title', 'Sales'); ?>

<?php $__env->startSection('third_party_stylesheets'); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <a href="<?php echo e(route('sales.create')); ?>" class="btn btn-primary">
                            Add Sale <i class="bi bi-plus"></i>
                        </a>

                        <hr>

                        <div class="table-responsive">
                            <?php echo $dataTable->table(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page_scripts'); ?>
    <?php echo $dataTable->scripts(); ?>

    <script>
        // Simple and direct print approach
        let printInProgress = false;
        
        function printSaleNota(saleId) {
            if (printInProgress) {
                return false;
            }
            
            printInProgress = true;
            
            // Simple popup approach - user manually clicks print
            const url = '<?php echo e(url("/sales/pos/print")); ?>/' + saleId;
            const printWindow = window.open(url, 'receipt_' + Date.now(), 
                'width=400,height=600,scrollbars=no,resizable=no');
            
            if (printWindow) {
                printWindow.focus();
            } else {
                alert('Please allow popups for printing receipts');
            }
            
            // Reset flag after short delay
            setTimeout(function() {
                printInProgress = false;
            }, 2000);
            
            return false;
        }

        // Compatibility flag for DataTable
        window.printInProgress = false;
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Sale\Resources/views/index.blade.php ENDPATH**/ ?>