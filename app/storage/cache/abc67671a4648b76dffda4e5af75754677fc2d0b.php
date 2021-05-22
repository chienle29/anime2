<div class="tc-settings-title">
    <h3><?php echo e(__('Category Page Settings')); ?></h3>
    <span><?php echo e(__("A category page is a page where URLs of the movies exist. For example, a page listing many news in a news
    site, a page listing many hotels in a booking site or a page showing many products in an e-commerce site can be
    considered as category pages. Here, you can define URLs of the categories of target site and CSS selectors that find
    post URLs so that the plugin can find and save posts automatically.")); ?></span>
</div>

<table class="tc-settings">

    
    <?php echo $__env->make('form-items.combined.input-with-label', [
        'name'          => \CTMovie\Model\Settings::MAIN_PAGE_URL,
        'title'         => __('Main URL'),
        'default'       => 'https://gogoanime.vc',
        'placeholder'   =>  __('Main URL of the target site'),
        'type'          => 'url',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.input-with-label', [
        'name'          =>  \CTMovie\Model\Settings::CATEGORY_MAP,
        'title'         =>  __('Category URLs'),
        'default'       => 'https://gogoanime.vc/anime-movies.html',
        'placeholder'   =>  __('Category URL from the target site...'),
        'type'          => 'url',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          =>  \CTMovie\Model\Settings::MOVIE_URL_IN_CATE_SELECTOR,
        'name2'         =>  \CTMovie\Model\Settings::MOVIE_URL_IN_CATE_SELECTOR_ATTR,
        'title'         =>  __('Movie URL Selectors'),
        'placeholder'   =>  __('Movie URL from the target site...'),
        'placeholder2'  =>  __('Attribute (default: href)'),
        'default'       =>  '.last_episodes ul.items li a',
        'default2'      =>  'href',
        'type'          => 'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('partials.table-section-title', ['title' => __("Next Page")], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.multiple-input-with-attribute', [
        'name'          => \CTMovie\Model\Settings::NEXT_PAGE_SELECTOR,
        'name2'         => \CTMovie\Model\Settings::NEXT_PAGE_SELECTOR_ATTR,
        'default'       => '.pagination ul.pagination-list li a',
        'default2'       => 'href',
        'title'         => __('Category Next Page URL Selectors'),
        'placeholder'   =>  __('Selector'),
        'placeholder2'   =>  __('Attribute (default: href)'),
        'type'          => 'text'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('partials.table-section-title', ['title' => __("Unnecessary Elements")], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('form-items.combined.input-with-label', [
        'name'  =>  \CTMovie\Model\Settings::UNNECESSARY_ELEMENT,
        'title' =>  __('Unnecessary Element Selectors'),
        'placeholder'   =>  __('Selector'),
        'type'  => 'text',
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</table>
<?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/site-settings/tab-category.blade.php ENDPATH**/ ?>