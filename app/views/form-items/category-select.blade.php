{{--
    Required variables:
        string $name        Name of the form item
        array $categories   Retrieved from Utils::getCategories()
--}}

<div class="input-group">
    <div class="input-container">
        @include('form-items.partials.categories', [
            'name'          => $name,
            'categories'    => $categories,
        ])
    </div>
</div>