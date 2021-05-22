(function($) {
    $(document).ready(function() {

        $('#get_gdrive').on("click", function (e) {
            e.preventDefault();
            $(".spinner").addClass("is-active");
            $.ajax({
                type : "post",
                dataType : "json",
                url : ajaxurl,
                data : {
                    action: "authentication_gdrive",
                    url_redirect: '/abc'
                },
                success: function(response) {
                    $('#oauth_gdrive').css('display', 'block');
                    $('#link_oauth_gdrive').attr('href', response.oauthGDrive_url);
                    $('.refesh-token').hide();
                },
                error: function( jqXHR, textStatus, errorThrown ){
                    console.log( 'The following error occured: ' + textStatus, errorThrown );
                },
            }).done(function () {
                $(".spinner").removeClass("is-active");
            });
            return false;
        });
    });
})(jQuery);