<table class="wcc-settings">
    <p>Chúng tôi sử dụng cron để tạo series và phim.</p>
    <p>Việc download video, upload google drive và cập nhật iframe cho anime. Vui lòng chạy command line.</p>

    {{-- LAU API --}}
    @include('form-items.combined.input-with-label', [
        'name'          =>  \CTMovie\Model\Settings::LAU_API_KEY,
        'title'         =>  __('Lậu api key'),
        'placeholder'   =>  __('Enter lậu api key'),
        'value'         => get_option(\CTMovie\Model\Settings::LAU_API_KEY),
        'type'          => 'text',
    ])

</table>
