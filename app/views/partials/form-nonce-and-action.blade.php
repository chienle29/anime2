{{--
    Required:
        string $pageActionKey

    Optional:
        bool $noNonceAndAction
--}}
<?php /** @var string $pageActionKey */ ?>
@if(!isset($noNonceAndAction) || !$noNonceAndAction)
    <?php wp_nonce_field($pageActionKey, \CTMovie\Environment::FORM_NONCE_NAME); ?>

    <input type="hidden" name="action" value="{{ $pageActionKey }}" id="hiddenaction">
@endif