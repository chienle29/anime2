<?php if(isset($_GET["success"])): ?>
    <?php $message = isset($_GET["message"]) && $_GET["message"] ? urldecode($_GET["message"]) : null; ?>
    <?php echo $__env->make('partials/alert', [
        'type'      =>  $_GET["success"] == 'true' ? 'success' : 'error',
        'message'   =>  $_GET["success"] == 'true' ?
                        ($message ?: '') :
                        ($message ?: __("An error occurred."))
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/partials/success-alert.blade.php ENDPATH**/ ?>