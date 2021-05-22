<?php
    /** @var string $name */
    /** @var string|array $optionData */
?>
<div class="input-group">
    <div class="input-container">
        <select name="<?php echo e($name); ?>" id="<?php echo e($name); ?>">
            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $optionData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>" <?php if($isOption && $key == $isOption): ?> selected="selected" <?php endif; ?>><?php echo e($optionData); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/form-items/select.blade.php ENDPATH**/ ?>