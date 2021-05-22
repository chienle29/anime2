

<tr <?php if(isset($id)): ?> id="<?php echo e($id); ?>" <?php endif; ?>
<?php if(isset($class)): ?> class="<?php echo e($class); ?>" <?php endif; ?>
    aria-label="<?php echo e($name); ?>"
>
    <td>
        <?php echo $__env->make('form-items/label', [
            'for'   => $name,
            'title' => $title,
            'info'  => $info,
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </td>
    <td>
        <div class="multi-input-text">
            <?php echo $__env->make('form-items/text', [
                'name'      => $name,
                'default'   => $default,
                'type'      => isset($type) && $type ? $type : null
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('form-items/text', [
                'name'          => $name2,
                'default'       => $default2,
                'placeholder'   => $placeholder2,
                'type'          => isset($type) && $type ? $type : null
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </td>
</tr><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/form-items/combined/multiple-input-with-attribute.blade.php ENDPATH**/ ?>