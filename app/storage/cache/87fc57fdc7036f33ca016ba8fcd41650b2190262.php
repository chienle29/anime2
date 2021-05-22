<div class="tc-settings-title">
    <h3><?php echo e(__('Post Page Settings')); ?></h3>
    <span><?php echo e(__("A post page is a page that contains the data that can be used to create posts in your site. For
    example, an article page of a blog, a product page of an e-commerce site or a hotel's page in a booking site can be
    considered as post pages. Here, you can configure many settings to define what information should be saved from
    target post pages.")); ?></span>
</div>

<table class="tc-settings">

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_URL_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_URL_SELECTOR_ATTR,
        'title'         =>  __('Video Url Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: src)'),
        'default'       =>  '.play-video iframe',
        'default2'      =>  'src',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_TITLE_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_TITLE_SELECTOR_ATTR,
        'title'         =>  __('Movie Title Selectors'),
        'placeholder'   =>  __('Selector'),
        'default'       =>  'h1',
        'placeholder2'  =>  __('Attribute (default: text)'),
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_DESCRIPTION_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_DESCRIPTION_SELECTOR_ATTR,
        'title'         =>  __('Movie description Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: html)'),
        'default'       =>  '.anime_info_body p.type:nth-child(3)',
        'default2'      =>  'html',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_STATUS_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_STATUS_SELECTOR_ATTR,
        'title'         =>  __('Movie status Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: text)'),
        'default'       =>  '.anime_info_body p:nth-child(8)',
        'default2'      =>  'text',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_RELEASED_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_RELEASED_SELECTOR_ATTR,
        'title'         =>  __('Movie released Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: text)'),
        'default'       =>  '.anime_info_body p:nth-child(7)',
        'default2'      =>  'text',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_CHAPTER_URL_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_CHAPTER_URL_SELECTOR_ATTR,
        'title'         =>  __('Movie chapter url Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: href)'),
        'default'       =>  'ul#episode_related li a',
        'default2'      =>  'href',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_EPISODE,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_EPISODE_ATTR,
        'title'         =>  __('Movie episode'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: ep_end)'),
        'default'       =>  'ul#episode_page li a',
        'default2'      =>  'ep_end',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('partials.table-section-title', ['title' => __("Featured Image")], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::THUMBNAIL_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::THUMBNAIL_SELECTOR_ATTR,
        'title'         =>  __('Featured Image Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'  =>  __('Attribute (default: src)'),
        'default'       =>  '.anime_info_body_bg img',
        'default2'      =>  'src',
        'type'          =>  'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</table>
<?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/site-settings/tab-post.blade.php ENDPATH**/ ?>