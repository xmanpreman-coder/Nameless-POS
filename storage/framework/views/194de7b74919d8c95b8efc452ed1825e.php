<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <meta content="Fahim Anzam Dip" name="author">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo e(asset('images/favicon.png')); ?>">

    <?php echo $__env->make('includes.main-css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>

<body class="c-app">
    <?php echo $__env->make('layouts.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="c-wrapper">
        <header class="c-header c-header-light c-header-fixed">
            <?php echo $__env->make('layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <div class="c-subheader justify-content-between px-3">
                <?php echo $__env->yieldContent('breadcrumb'); ?>
            </div>
        </header>

        <div class="c-body">
            <main class="c-main">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>

        <?php echo $__env->make('layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <?php echo $__env->make('includes.main-js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Backup button functionality
            const backupButton = document.getElementById('backup-database-button');
            if (backupButton) {
                backupButton.addEventListener('click', async () => {
                    console.log('Backup button clicked. Calling electronAPI.backupDatabase...');
                    try {
                        const result = await window.electronAPI.backupDatabase();
                        console.log('Backup process finished:', result);
                    } catch (error) {
                        console.error('An error occurred while trying to backup the database:', error);
                    }
                });
            }
            
            // Fix sidebar active state issues
            setTimeout(function() {
                // Reset all sidebar menu items to proper state based on current route
                const currentUrl = window.location.href;
                const sidebarItems = document.querySelectorAll('.c-sidebar-nav-item');
                
                sidebarItems.forEach(function(item) {
                    const links = item.querySelectorAll('a');
                    let isActive = false;
                    
                    links.forEach(function(link) {
                        if (link.href === currentUrl || currentUrl.includes(link.getAttribute('href'))) {
                            isActive = true;
                        }
                    });
                    
                    // Remove c-active class from all items first
                    item.classList.remove('c-active');
                    
                    // Add c-active only to the current page's item
                    if (isActive) {
                        item.classList.add('c-active');
                    }
                });
                
                // Also fix dropdown menu states
                const dropdownItems = document.querySelectorAll('.c-sidebar-nav-dropdown');
                dropdownItems.forEach(function(dropdown) {
                    const activeChild = dropdown.querySelector('.c-sidebar-nav-dropdown-item.c-active');
                    if (activeChild) {
                        dropdown.classList.add('c-show');
                    } else {
                        dropdown.classList.remove('c-show');
                    }
                });
            }, 200);
        });
    </script>
</body>
</html>
<?php /**PATH D:\project warnet\Nameless\resources\views/layouts/app.blade.php ENDPATH**/ ?>