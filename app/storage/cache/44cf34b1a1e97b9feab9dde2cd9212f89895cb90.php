<div class="wrap container-general-settings">
    <h1><?php echo e(__('Thiết lập')); ?></h1>
    <?php echo $__env->make('partials.success-alert', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <form action="admin-post.php" method="post" id="post">
        
        <?php echo $__env->make('partials.form-nonce-and-action', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="details">
            <div class="inside">
                <div class="panel-wrap wcc-settings-meta-box wcc-general-settings">

                    <?php echo $__env->make('general-settings/settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                </div>
            </div>
        </div>

        
        <?php echo $__env->make('form-items.partials.form-button-container', ['id' => 'submit-bottom', 'info' => ''], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </form>

</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/general-settings/main.blade.php ENDPATH**/ ?>