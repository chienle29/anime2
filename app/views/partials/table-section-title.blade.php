<tr data-id="section-{{ \Illuminate\Support\Str::slug($title) }}" @if(isset($class) && $class) class="{{ $class }}" @endif>
    <td colspan="2" style="width: 100%">
        <h4 class="section-title">{!! $title !!}</h4>
    </td>
</tr>