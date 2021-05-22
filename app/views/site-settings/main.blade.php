<div class="panel-wrap tc-settings-meta-box" data-post-id="{{ $postId }}">
    <h2 class="nav-tab-wrapper fixable">
        <a href="#" data-tab="#tab-category"    class="nav-tab nav-tab-active">{{ __('Category') }}</a>
        <a href="#" data-tab="#tab-post"        class="nav-tab">{{ __('Movie') }}</a>
    </h2>
</div>

{{-- CATEGORY PAGE SETTINGS --}}
<div id="tab-category" class="tab">
    @include('site-settings.tab-category')
</div>

{{-- POST PAGE SETTINGS --}}
<div id="tab-post" class="tab hidden">
    @include('site-settings.tab-post')
</div>