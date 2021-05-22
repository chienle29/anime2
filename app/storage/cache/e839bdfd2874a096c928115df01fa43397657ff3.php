<div class="panel-wrap tc-settings-meta-box" data-post-id="<?php echo e($postId); ?>">
    <h2 class="nav-tab-wrapper fixable">
        <a href="#" data-tab="#tab-category"    class="nav-tab nav-tab-active"><?php echo e(__('Category')); ?></a>
        <a href="#" data-tab="#tab-post"        class="nav-tab"><?php echo e(__('Movie')); ?></a>
    </h2>
</div>


<div id="tab-category" class="tab">
    <?php echo $__env->make('site-settings.tab-category', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>


<div id="tab-post" class="tab hidden">
    <?php echo $__env->make('site-settings.tab-post', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/site-settings/main.blade.php ENDPATH**/ ?>