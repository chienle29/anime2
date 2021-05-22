<?php
/** @var string $name */
$val = isset($value) ? $value : (isset($settings[$name]) ? (isset($isOption) && $isOption ? $settings[$name] : $settings[$name][0]) : '');
$val = isset($inputKey) && $inputKey && isset($val[$inputKey]) ? $val[$inputKey] : $val;

$inputKeyVal = isset($inputKey) && $inputKey ? "[{$inputKey}]" : '';
$default = $default ?: null;
if (empty($val) && !empty($default)) {
    $val = $default;
}

?>

<div class="input-group text
    <?php echo e(isset($addon) ? ' addon ' : ''); ?>

    <?php echo e(isset($remove) ? ' remove ' : ''); ?>

    <?php echo e(isset($showDevTools) && $showDevTools ? ' dev-tools ' : ''); ?>

    <?php echo e(isset($class) ? ' ' . $class . ' ' : ''); ?>"
     <?php if(isset($dataKey)): ?> data-key="<?php echo e($dataKey); ?>" <?php endif; ?>
>
    <?php if(isset($addon)): ?>
        <?php echo $__env->make('form-items.partials.button-addon-test', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <?php if(isset($showDevTools) && $showDevTools): ?>
        <?php echo $__env->make('form-items.dev-tools.button-dev-tools', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
    <div class="input-container">
        <input type="<?php echo e(isset($type) && $type ? $type : 'text'); ?>"
               <?php if(isset($min)): ?> min="<?php echo e($min); ?>" <?php endif; ?>
               id="<?php echo e(isset($name) ? $name : ''); ?><?php echo e($inputKeyVal); ?>"
               name="<?php echo e(isset($name) ? $name : ''); ?><?php echo e($inputKeyVal); ?>"
               value="<?php echo e($val); ?>"
               placeholder="<?php echo e(isset($placeholder) ? $placeholder : ''); ?>"
               <?php if(isset($required)): ?> required="required" <?php endif; ?>
               <?php if(isset($inputClass)): ?> class="<?php echo e($inputClass); ?>" <?php endif; ?>
               <?php if(isset($step)): ?> step="<?php echo e($step); ?>" <?php endif; ?>
               <?php if(isset($maxlength) && $maxlength): ?> maxlength="<?php echo e($maxlength); ?>" <?php endif; ?>
        />
    </div>
    <?php if(isset($remove)): ?>
        <?php echo $__env->make('form-items/remove-button', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/form-items/text.blade.php ENDPATH**/ ?>