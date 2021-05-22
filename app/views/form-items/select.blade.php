<?php
    /** @var string $name */
    /** @var string|array $optionData */
?>
<div class="input-group">
    <div class="input-container">
        <select name="{{ $name }}" id="{{ $name }}">
            @foreach($options as $key => $optionData)
                <option value="{{ $key }}" @if($isOption && $key == $isOption) selected="selected" @endif>{{ $optionData }}</option>
            @endforeach
        </select>
    </div>
</div>