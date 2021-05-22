
<?php /** @var string $pageActionKey */ ?>
<?php if(!isset($noNonceAndAction) || !$noNonceAndAction): ?>
    <?php wp_nonce_field($pageActionKey, \CTMovie\Environment::FORM_NONCE_NAME); ?>

    <input type="hidden" name="action" value="<?php echo e($pageActionKey); ?>" id="hiddenaction">
<?php endif; ?><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/partials/form-nonce-and-action.blade.php ENDPATH**/ ?>