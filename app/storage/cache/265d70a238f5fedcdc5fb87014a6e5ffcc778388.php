<div class="wrap container-tools" id="container-tools">
    <h1><?php echo e(__('Tools')); ?></h1>
    <div id="tc-tools">
        <form action="" class="tool-form tool-manual-crawl">

            <input type="hidden" name="tool_type" value="save_post">

            <div class="panel-wrap">

                <table class="tc-settings">
                    
                    <?php echo $__env->make('form-items.combined.select-with-label', [
                        'name'      =>  \CTMovie\Model\Settings::TOOLS_CAMPAIGN,
                        'title'     =>  __('Campaign'),
                        'options'   =>  $campaigns,
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    
                    <?php echo $__env->make('form-items.combined.input-with-label', [
                        'name'  =>  \CTMovie\Model\Settings::TOOLS_MOVIE_URL,
                        'title' =>  __('Post URLs'),
                        'placeholder'   =>  __('New line separated post URLs...'),
                        'type'  => 'url',
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                </table>

                
                <div class="button-container">
                    
                    <?php echo $__env->make('form-items/submit-button', [
                        'text'  =>  __('Crawl now'),
                        'class' => 'crawl-now',
                        'title' => __('The URLs you entered will be crawled one by one, as soon as you click this. Your browser needs to stay open until all URLs are finished being crawled.'),
                        'id'    => 'crawl-movie'
                    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>

                

            </div>
        </form>
    </div>
    <div id="ct-movie-container">

    </div>
</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/tools/main.blade.php ENDPATH**/ ?>