(function($) {
    $(document).ready(function() {
        $('.nav-tab').on('click', function () {
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            let idTab = $(this).attr('data-tab');
            $('.tab').addClass('hidden');
            $(idTab).removeClass('hidden');
        });
        $("#crawl-movie").on("click", function (e) {
            e.preventDefault();
            $.ajax({
                type : "post",
                dataType : "json",
                url : ajaxurl,
                data : {
                    action: "movie_craw",
                    tool_type: 'get_post_urls_from_category_url',
                    movie_url : $('#_ct_tools_movie_url').val(),
                    campaign_id: $('#_ct_tools_campaign').val()
                },
                success: function(response) {
                    $('#ct-movie-container').html(response['view']);
                },
                error: function( jqXHR, textStatus, errorThrown ){
                    console.log( 'The following error occured: ' + textStatus, errorThrown );
                }
            })
            return false;
        });
    });
})(jQuery);