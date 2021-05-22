{{--
    Required variables:
        String $title:  Title of the form item. Label name.
        String $info:   Information about the form item
        String $name:   Name of the form item
        array $categories: See category-select form item view

    Optional variables:
        String $id:     ID of the <tr> element surrounding the form items
        String $class:  Class of the <tr> element surrounding the form items.
        Other variables of label and category-select form item views.

--}}

<tr @if(isset($id)) id="{{ $id }}" @endif
    @if(isset($class)) class="{{ $class }}" @endif
    aria-label="{{ $name }}"
>
    <td>
        @include('form-items/label', [
            'for'   =>  $name,
            'title' =>  $title,
            'info'  =>  $info
        ])
    </td>
    <td>
        @include('form-items/category-select', [
            'name'          =>  $name,
            'categories'    =>  $categories,
        ])
    </td>
</tr>