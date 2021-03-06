<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! Theme::get('title') !!}</title>
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{--<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">--}}
    @if(isset(Theme::get('basis_config')['css_adaptive']) && Theme::get('basis_config')['css_adaptive'] == 1)
        <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
    @else
        <meta name="viewport" content="initial-scale=0.1">
    @endif
    <link rel="shortcut icon" href="{{ Theme::asset()->url('images/favicon.ico') }}" />
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="/themes/default/assets/plugins/bootstrap/css/bootstrap.min.css">
    {!! Theme::asset()->container('specific-css')->styles() !!}
    <link rel="stylesheet" href="/themes/default/assets/plugins/ace/css/ace.min.css">
    <link rel="stylesheet" href="/themes/default/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/themes/default/assets/css/main.css">
    <link rel="stylesheet" href="/themes/default/assets/css/header.css">
    <link rel="stylesheet" href="/themes/default/assets/css/footer.css">
    <link rel="stylesheet" href="/themes/default/assets/css/usercenter/finance/finance-layout.css">
    <link rel="stylesheet" href="/themes/default/assets/css/{!! Theme::get('color') !!}/style.css">
    <link rel="stylesheet" href="/themes/default/assets/css/{!! Theme::get('color') !!}/user.css">
    {!! Theme::asset()->container('custom-css')->styles() !!}
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?949b5c339862bc29eee5b0502946fa77";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
</head>
<body>
<header>
    <div class="header-top">
        <div class="container clearfix col-left">

                {!! Theme::partial('usernav') !!}

        </div>
    </div>
</header>

<section>
    {!! Theme::content() !!}
</section>

<footer>
    {!! Theme::partial('footer') !!}
</footer>


<script src="/themes/default/assets/js/doc/jquery.min.js"></script>
<script src="/themes/default/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/themes/default/assets/js/common.js"></script>
{{--<script src="/themes/default/assets/js/usercenter.js"></script>--}}
{!! Theme::asset()->container('specific-js')->scripts() !!}
{!! Theme::asset()->container('custom-js')->scripts() !!}

</body>
</html>