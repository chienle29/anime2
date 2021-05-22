<div class="wrap container-general-settings">
    <h1>{{ __('Thiết lập') }}</h1>
    @include('partials.success-alert')
    <form action="admin-post.php" method="post" id="post">
        {{-- ADD NONCE AND ACTION --}}
        @include('partials.form-nonce-and-action')

        <div class="details">
            <div class="inside">
                <div class="panel-wrap wcc-settings-meta-box wcc-general-settings">

                    @include('general-settings/settings')

                </div>
            </div>
        </div>

        {{-- SUBMIT BUTTON --}}
        @include('form-items.partials.form-button-container', ['id' => 'submit-bottom', 'info' => ''])
    </form>

</div>