{{--
    type: can be "success", "warning", "error", "info"
--}}
@if(!empty($message))
<div id="message" class="notice notice-{{ isset($type) ? $type : 'info' }} is-dismissible">
    <p>{{ isset($message) ? $message : __('Done.') }}</p>
</div>
@endif