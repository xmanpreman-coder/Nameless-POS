<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <meta content="Fahim Anzam Dip" name="author">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
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
    
    <!-- Scanner Utils (Load first) -->
    <script src="<?php echo e(asset('js/scanner-utils.js')); ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Backup button functionality
            const backupButton = document.getElementById('backup-database-button');
            if (backupButton) {
                backupButton.addEventListener('click', async () => {
                    console.log('Backup button clicked...');
                    
                    // Check if we're in Electron mode or web mode
                    if (window.electronAPI && window.electronAPI.backupDatabase) {
                        // Electron mode - use Electron API
                        console.log('Using Electron API for backup');
                        
                        // Disable button during backup
                        backupButton.disabled = true;
                        const originalText = backupButton.innerHTML;
                        backupButton.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Backing up...';
                        
                        try {
                            const result = await window.electronAPI.backupDatabase();
                            console.log('Electron backup process finished:', result);
                            
                            if (result.success) {
                                alert('Backup created successfully: ' + result.fileName);
                                console.log('Backup created successfully:', result.fileName);
                            } else {
                                alert('Backup failed: ' + result.message);
                                console.error('Backup failed:', result.message);
                            }
                        } catch (error) {
                            console.error('An error occurred during Electron backup:', error);
                            alert('Error during backup: ' + error.message);
                        } finally {
                            // Re-enable button
                            backupButton.disabled = false;
                            backupButton.innerHTML = originalText;
                        }
                    } else {
                        // Web mode - redirect to backup page
                        console.log('Using web interface for backup');
                        window.location.href = '<?php echo e(route("database.backup.index")); ?>';
                    }
                });
            }
            
            // Fix sidebar active state issues - using requestAnimationFrame for better timing
            requestAnimationFrame(function() {
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
                    const activeChild = dropdown.querySelector('.c-sidebar-nav-dropdown-items .c-sidebar-nav-item .c-sidebar-nav-link.c-active');
                    if (activeChild) {
                        dropdown.classList.add('c-show');
                    } else {
                        dropdown.classList.remove('c-show');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php /**PATH D:\project warnet\Nameless\resources\views/layouts/app.blade.php ENDPATH**/ ?>