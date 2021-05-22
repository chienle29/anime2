<button class="button button-primary button-large {{ isset($class) && $class ? $class : '' }}"
        id="{{ !empty($id) ? $id : '' }}">
    {{ __($text) }}
</button>