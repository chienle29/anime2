<select name="<?php echo e($name); ?>" id="<?php echo e($name); ?>">
    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            /** @var array $categoryData */
            $categoryId         = $categoryData['id'];
            $categoryName       = $categoryData['name'];
            $isSelected         = isset($selectedId) && $selectedId && $categoryId == $selectedId;
        ?>
        <option value="<?php echo e($categoryId); ?>"
                <?php if($isSelected): ?> selected="selected" <?php endif; ?>><?php echo e($categoryName); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/form-items/partials/categories.blade.php ENDPATH**/ ?>