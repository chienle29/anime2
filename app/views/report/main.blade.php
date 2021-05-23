<div class="content">
    <div class="container">
        <h2 class="mb-5">Thống kê</h2>
        <div class="row mb-5">
            <div class="col-md-3 mb-4 stretch-card transparent">
                <div class="card card-tale" style="background: #7DA0FA">
                    <div class="card-body">
                        <p class="mb-4">URL series đã thu thập</p>
                        <p class="fs-30 mb-2">{{$countUrl}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 stretch-card transparent">
                <div class="card card-dark-blue" style="background: #4747a1">
                    <div class="card-body">
                        <p class="mb-4">Tổng series đã lưu</p>
                        <p class="fs-30 mb-2">{{$countSeriesSaved}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 stretch-card transparent">
                <div class="card card-dark-blue" style="background: #7978e9">
                    <div class="card-body">
                        <p class="mb-4">Tổng số URL tập phim đã thu thập</p>
                        <p class="fs-30 mb-2">{{$countAnimeUrl}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 stretch-card transparent">
                <div class="card card-dark-blue" style="background: #f3797e">
                    <div class="card-body">
                        <p class="mb-4">Số tập phim đã lưu</p>
                        <p class="fs-30 mb-2">{{$countAnimeSaved}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        font-family: "Roboto", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        background-color: #fff;
        font-weight: 300; }

    p {
        font-weight: 300; }

    h1, h2, h3, h4, h5, h6,
    .h1, .h2, .h3, .h4, .h5, .h6 {
        font-family: "Roboto", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; }

    a {
        -webkit-transition: .3s all ease;
        -o-transition: .3s all ease;
        transition: .3s all ease; }
    a, a:hover {
        text-decoration: none !important; }


    h2 {
        font-size: 20px; }

    .custom-table {
        min-width: 900px; }
    .custom-table thead tr, .custom-table thead th {
        padding-bottom: 30px;
        border-top: none;
        border-bottom: none !important;
        color: #000; }
    .custom-table tbody th, .custom-table tbody td {
        color: #777;
        font-weight: 400;
        padding-bottom: 20px;
        padding-top: 20px;
        font-weight: 300;
        border: none;
        -webkit-transition: .3s all ease;
        -o-transition: .3s all ease;
        transition: .3s all ease; }
    .custom-table tbody th small, .custom-table tbody td small {
        color: #b3b3b3;
        font-weight: 300; }
    .custom-table tbody tr {
        -webkit-transition: .3s all ease;
        -o-transition: .3s all ease;
        transition: .3s all ease; }
    .custom-table .td-box-wrap {
        padding: 0; }
    .custom-table .box {
        background: #fff;
        border-radius: 4px;
        margin-top: 15px;
        margin-bottom: 15px; }
    .custom-table .box td, .custom-table .box th {
        border: none !important; }

    .custom-control.ios-switch {
        --color: #4cd964;
        padding-left: 0; }
    .custom-control.ios-switch .ios-switch-control-input {
        display: none; }
    .custom-control.ios-switch .ios-switch-control-input:active ~ .ios-switch-control-indicator::after {
        width: 20px; }
    .custom-control.ios-switch .ios-switch-control-input:checked ~ .ios-switch-control-indicator {
        border: 10px solid var(--color); }
    .custom-control.ios-switch .ios-switch-control-input:checked ~ .ios-switch-control-indicator::after {
        top: -8px;
        left: 4px; }
    .custom-control.ios-switch .ios-switch-control-input:checked:active ~ .ios-switch-control-indicator::after {
        left: 0px; }
    .custom-control.ios-switch .ios-switch-control-input:disabled ~ .ios-switch-control-indicator {
        opacity: .4; }
    .custom-control.ios-switch .ios-switch-control-indicator {
        display: inline-block;
        position: relative;
        margin: 0 10px;
        top: 4px;
        width: 32px;
        height: 20px;
        background: #fff;
        border-radius: 16px;
        -webkit-transition: .3s;
        -o-transition: .3s;
        transition: .3s;
        border: 2px solid #ddd; }
    .custom-control.ios-switch .ios-switch-control-indicator::after {
        content: '';
        display: block;
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 16px;
        -webkit-transition: .3s;
        -o-transition: .3s;
        transition: .3s;
        top: 0px;
        left: 0px;
        background: #fff;
        -webkit-box-shadow: 0 0 2px #aaa, 0 2px 5px #999;
        box-shadow: 0 0 2px #aaa, 0 2px 5px #999; }

    /* Custom Checkbox */
    .control {
        display: block;
        position: relative;
        margin-bottom: 25px;
        cursor: pointer;
        font-size: 18px; }

    .control input {
        position: absolute;
        z-index: -1;
        opacity: 0; }

    .control__indicator {
        position: absolute;
        top: 2px;
        left: 0;
        height: 20px;
        width: 20px;
        border-radius: 4px;
        border: 2px solid #ccc;
        background: transparent; }

    .control--radio .control__indicator {
        border-radius: 50%; }

    .control:hover input ~ .control__indicator,
    .control input:focus ~ .control__indicator {
        border: 2px solid #007bff; }

    .control input:checked ~ .control__indicator {
        border: 2px solid #007bff;
        background: #007bff; }

    .control input:disabled ~ .control__indicator {
        background: #e6e6e6;
        opacity: 0.6;
        pointer-events: none;
        border: 2px solid #ccc; }

    .control__indicator:after {
        font-family: 'icomoon';
        content: '\e5ca';
        position: absolute;
        display: none; }

    .control input:checked ~ .control__indicator:after {
        display: block;
        color: #fff; }

    .control--checkbox .control__indicator:after {
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -52%);
        -ms-transform: translate(-50%, -52%);
        transform: translate(-50%, -52%); }

    .control--checkbox input:disabled ~ .control__indicator:after {
        border-color: #7b7b7b; }

    .control--checkbox input:disabled:checked ~ .control__indicator {
        background-color: #007bff;
        opacity: .2;
        border: 2px solid #007bff; }
    .card {
        border: none;
        color: #fff;
        max-height: 150px;
    }
    .card-body p {
        font-size: 16px;
    }
    .card-body p:last-child {
        font-size: 25px;
        font-weight: bold;
    }
    @media (min-width: 1200px){
        .col-md-3{
            flex: 0 0 25%;
            max-width: 22%;
        }
    }

    .row{
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }
    .col-md-3{
        position: relative;
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
    }
</style>