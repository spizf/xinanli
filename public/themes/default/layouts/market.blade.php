<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! strip_tags(Theme::get('title'),'<img>') !!}</title>
    <meta name="keywords" style="display:none;" content="{!!  Theme::get('keywords') !!}">
    <meta name="description" content="{!! Theme::get('description') !!}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @if(Theme::get('engine_status')==1)
    <meta name="robots" content="noindex,follow">
    @endif
    @if(isset(Theme::get('basis_config')['css_adaptive']) && Theme::get('basis_config')['css_adaptive'] == 1)
        <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
    @else
        <meta name="viewport" content="initial-scale=0.1">
    @endif
    <meta property="qc:admins" content="232452016063535256654" />
    <meta property="wb:webmaster" content="19a842dd7cc33de3" />
    <link rel="shortcut icon" href="{{ Theme::asset()->url('images/favicon.ico') }}" />
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" type="text/css" href="/themes/default/assets/market/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/themes/default/assets/market/css/fdywstyle.css"/>
    <link rel="stylesheet" href="/themes/default/assets/css/footer.css">
</head>
<body>

<header>
    {!! Theme::partial('marketheader') !!}
</header>
<section>
    {!! Theme::content() !!}
</section>
<footer>
    {!! Theme::partial('marketfooter') !!}
</footer>
</body>
