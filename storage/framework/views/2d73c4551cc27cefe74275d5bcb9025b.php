<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <title>Login | <?php echo e(config('app.name')); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo e(asset('images/favicon.png')); ?>">
    <!-- CoreUI CSS -->
    <link rel="stylesheet" href="<?php echo e(mix('css/app.css')); ?>" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</head>

<body class="c-app flex-row align-items-center">
<div class="container">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-center">
            <?php
                try {
                    $settings = \Modules\Setting\Entities\Setting::first();
                    $companyName = $settings ? $settings->company_name : 'Nameless.POS';
                    $showLogo = false;
                    
                    if ($settings && $settings->login_logo) {
                        $logoPath = storage_path('app/public/' . $settings->login_logo);
                        if (file_exists($logoPath)) {
                            $logoUrl = asset('storage/' . $settings->login_logo) . '?v=' . filemtime($logoPath);
                            $showLogo = true;
                        }
                    }
                } catch (\Exception $e) {
                    $companyName = 'Nameless.POS';
                    $showLogo = false;
                }
            ?>
            
            <?php if($showLogo): ?>
                <img width="200" src="<?php echo e($logoUrl); ?>" alt="<?php echo e($companyName); ?>" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none; font-size: 32px; font-weight: bold; color: #333; margin: 20px 0; text-align: center;">
                    <?php echo e($companyName); ?>

                </div>
            <?php else: ?>
                <div style="font-size: 32px; font-weight: bold; color: #333; margin: 20px 0; text-align: center;">
                    <?php echo e($companyName); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <?php if(Session::has('account_deactivated')): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo e(Session::get('account_deactivated')); ?>

                </div>
            <?php endif; ?>
            <div class="card p-4 border-0 shadow-sm">
                <div class="card-body">
                    <form id="login" method="post" action="<?php echo e(url('/login')); ?>">
                        <?php echo csrf_field(); ?>
                        <h1>Login</h1>
                        <p class="text-muted">Sign In to your account</p>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                    <span class="input-group-text">
                                      <i class="bi bi-person"></i>
                                    </span>
                            </div>
                            <input id="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   name="email" value="<?php echo e(old('email')); ?>"
                                   placeholder="Email">
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="input-group mb-4">
                            <div class="input-group-prepend">
                                    <span class="input-group-text">
                                      <i class="bi bi-lock"></i>
                                    </span>
                            </div>
                            <input id="password" type="password"
                                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   placeholder="Password" name="password">
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <button id="submit" class="btn btn-primary px-4 d-flex align-items-center"
                                        type="submit">
                                    Login
                                    <div id="spinner" class="spinner-border text-info" role="status"
                                         style="height: 20px;width: 20px;margin-left: 5px;display: none;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </button>
                            </div>
                            <div class="col-8 text-right">
                                <a class="btn btn-link px-0" href="<?php echo e(route('password.request')); ?>">
                                    Forgot password?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center mt-5 lead">
                Developed By
                <a href="https://ubed666.com" class="font-weight-bold text-primary">Ubed666</a>
            </p>
        </div>
    </div>
</div>

<!-- CoreUI -->
<script src="<?php echo e(mix('js/app.js')); ?>" defer></script>
<script>
    let login = document.getElementById('login');
    let submit = document.getElementById('submit');
    let email = document.getElementById('email');
    let password = document.getElementById('password');
    let spinner = document.getElementById('spinner')

    login.addEventListener('submit', (e) => {
        submit.disabled = true;
        email.readonly = true;
        password.readonly = true;

        spinner.style.display = 'block';

        login.submit();
    });

    setTimeout(() => {
        submit.disabled = false;
        email.readonly = false;
        password.readonly = false;

        spinner.style.display = 'none';
    }, 3000);
</script>

</body>
</html>
<?php /**PATH D:\project warnet\Nameless\resources\views/auth/login.blade.php ENDPATH**/ ?>