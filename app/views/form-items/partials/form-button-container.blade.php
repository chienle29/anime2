{{--
    Optional variables:
        string $class:          CSS class that will be added to the container
        string $id:             ID of the submit button
        string $buttonText:     Text to be shown in the button
        string $buttonTitle:    Title of the button
        string $dataPlacement:  Data placement option for tooltip. Default: 'right'
--}}

<div class="form-button-container {{ isset($class) ? $class : '' }}">
    <button class="button {{ isset($buttonClass) ? $buttonClass : 'button-primary' }} button-large" type="submit" id="{{ isset($id) ? $id : '' }}"
        @if(isset($buttonTitle) && $buttonTitle)
            title="{{ $buttonTitle }}" data-wpcc-toggle="wpcc-tooltip" data-placement="{{ isset($dataPlacement) && $dataPlacement ? $dataPlacement : 'right' }}"
        @endif
    >
        @if(isset($buttonText) && $buttonText)
            {{ $buttonText }}
        @else
            {{ __('Lưu thay đổi') }}
        @endif
    </button>
</div>