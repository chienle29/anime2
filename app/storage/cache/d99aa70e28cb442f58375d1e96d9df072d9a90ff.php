<?php
 $settings = $settings ?? [];
?>
<div style="margin-bottom: 50px">
    <h3>Get Client Google Drive</h3>

        <?php if(isset($settings['access_token_gdrive']) && $settings['access_token_gdrive']): ?>
        <div class="refesh-token">Google Drive authorized  <button class="button-large button-link" id="get_gdrive"> Refresh Token </button> </div>
        <?php else: ?>
            <div style="position: relative">
                <button class="button-secondary" id="get_gdrive">Get GDrive</button>
            </div>
        <?php endif; ?>
    <span class="spinner" style="float: none"></span>
    <div style="display: none" id="oauth_gdrive">
        <a href="" id="link_oauth_gdrive">Click to connect Google Drive</a>
    </div>

</div><?php /**PATH /var/www/html/truyentranh/wp-content/plugins/ct-movie-crawler/app/views/general-settings/gdrive.blade.php ENDPATH**/ ?>