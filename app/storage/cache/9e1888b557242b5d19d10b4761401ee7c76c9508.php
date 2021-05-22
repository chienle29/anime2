<table class="wcc-settings">

    
    <?php echo $__env->make('form-items.combined.checkbox-with-label', [
        'name'      =>  \CTMovie\Model\Settings::AUTO_CRAWL_MOVIE,
        'title'     =>  __('Tự động cào phim'),
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.select-with-label', [
        'name'      =>  \CTMovie\Model\Settings::COLLECT_URLS_INTERVAL,
        'title'     =>  __('Khoảng thời gian thu thập URL phim'),
        'options'   =>  $intervals,
        'isOption'  =>  $settings[\CTMovie\Model\Settings::COLLECT_URLS_INTERVAL],
        'id'        => 'url-collection-interval',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.select-with-label', [
        'name'      =>  \CTMovie\Model\Settings::CREATE_SERIES_INTERVAL,
        'title'     =>  __('Khoảng thời gian tạo series'),
        'options'   =>  $intervals,
        'isOption'  =>  $settings[\CTMovie\Model\Settings::CREATE_SERIES_INTERVAL],
        'id'        =>  'post-crawling-interval',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.select-with-label', [
        'name'      =>  \CTMovie\Model\Settings::CRAWL_ANIME_INTERVAL,
        'title'     =>  __('Khoảng thời gian tạo anime'),
        'options'   =>  $intervals,
        'isOption'  =>  $settings[\CTMovie\Model\Settings::CRAWL_ANIME_INTERVAL],
        'id'        =>  'post-crawling-interval',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.input-with-label', [
        'name'          =>  \CTMovie\Model\Settings::LAU_API_KEY,
        'title'         =>  __('Lậu api key'),
        'placeholder'   =>  __('Enter lậu api key'),
        'value'         => get_option(\CTMovie\Model\Settings::LAU_API_KEY),
        'type'          => 'text',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</table>
<?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/general-settings/settings.blade.php ENDPATH**/ ?>