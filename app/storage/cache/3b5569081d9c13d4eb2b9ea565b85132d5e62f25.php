<div class="input-group">
    <div class="input-container">
        <input type="checkbox"
               id="<?php echo e(isset($name) ? $name : ''); ?>"
               name="<?php echo e(isset($name) ? $name : ''); ?>"
               <?php if(isset($dependants) && $dependants): ?> data-dependants='<?php echo e($dependants); ?>' <?php endif; ?>
               <?php if(isset($settings[$name]) && !empty($settings[$name]) && $settings[$name][0]): ?> checked="checked" <?php endif; ?> />
    </div>
</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/form-items/checkbox.blade.php ENDPATH**/ ?>