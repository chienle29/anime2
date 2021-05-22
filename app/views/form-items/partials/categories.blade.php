<select name="{{ $name }}" id="{{ $name }}">
    @foreach($categories as $categoryData)
        <?php
            /** @var array $categoryData */
            $categoryId         = $categoryData['id'];
            $categoryName       = $categoryData['name'];
            $isSelected         = isset($selectedId) && $selectedId && $categoryId == $selectedId;
        ?>
        <option value="{{ $categoryId }}"
                @if($isSelected) selected="selected" @endif>{{ $categoryName }}</option>
    @endforeach
</select>