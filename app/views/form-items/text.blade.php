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
    {{ isset($addon) ? ' addon ' : '' }}
    {{ isset($remove) ? ' remove ' : '' }}
    {{ isset($showDevTools) && $showDevTools ? ' dev-tools ' : '' }}
    {{ isset($class) ? ' ' . $class . ' ' : '' }}"
     @if(isset($dataKey)) data-key="{{ $dataKey }}" @endif
>
    @if(isset($addon))
        @include('form-items.partials.button-addon-test')
    @endif

    @if(isset($showDevTools) && $showDevTools)
        @include('form-items.dev-tools.button-dev-tools')
    @endif
    <div class="input-container">
        <input type="{{ isset($type) && $type ? $type : 'text' }}"
               @if(isset($min)) min="{{ $min }}" @endif
               id="{{ isset($name) ? $name : '' }}{{ $inputKeyVal }}"
               name="{{ isset($name) ? $name : '' }}{{ $inputKeyVal }}"
               value="{{ $val }}"
               placeholder="{{ isset($placeholder) ? $placeholder : '' }}"
               @if(isset($required)) required="required" @endif
               @if(isset($inputClass)) class="{{ $inputClass }}" @endif
               @if(isset($step)) step="{{ $step }}" @endif
               @if(isset($maxlength) && $maxlength) maxlength="{{ $maxlength }}" @endif
        />
    </div>
    @if(isset($remove))
        @include('form-items/remove-button')
    @endif
</div>