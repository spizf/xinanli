<div class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-12 g-address col-left">
                <div>
                    @if(!empty(Theme::get('article_cate')))
                        @foreach(Theme::get('article_cate') as $item)
                            <a target="_blank" href="/article/aboutUs/{!! $item['id'] !!}">{!! $item['cate_name'] !!}</a>
                            <span></span>
                        @endforeach
                    @endif
                </div>
                <div class="space-6"></div>
                <p class="cor-gray87">公司名称：{!! Theme::get('site_config')['company_name'] !!} &nbsp;&nbsp;地址：{!! Theme::get('site_config')['company_address'] !!}</p>
                <p class="cor-gray87 kppw-tit">
                    {{--{!! config('kppw.kppw_powered_by') !!}
                    {!! config('kppw.kppw_version') !!}--}}
                    {!! Theme::get('site_config')['copyright'] !!}{!! Theme::get('site_config')['record_number'] !!}
                    <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1272420864'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s13.cnzz.com/z_stat.php%3Fid%3D1272420864%26show%3Dpic' type='text/javascript'%3E%3C/script%3E"));</script>
                </p>
            </div>
            <div class="col-lg-3 g-contact visible-lg-block hidden-sm hidden-md hidden-xs">
                <div class="cor-gray71 text-size14 g-contacthd"><span>联系方式</span></div>
                <div class="space-6"></div>
                <p class="cor-gray97">服务热线：{!! Theme::get('site_config')['phone'] !!}</p>
                <p class="cor-gray97">Email：{!! Theme::get('site_config')['Email'] !!}</p>
            </div>
            <div class="col-lg-3 focusus visible-lg-block hidden-sm hidden-md hidden-xs col-left" style="padding-top:0;">
                <div class="cor-gray71 text-size14 focusushd"><span>关注我们</span></div>
                <div class="space-8"></div>
                <div class="clearfix">
                    @if(Theme::get('site_config')['wechat']['wechat_switch'] == 1)
                    <div class="foc foc-bg">
                        <a class="focususwx foc-wx" href=""></a>
                        <div class="foc-ewm">
                            <div class="foc-ewm-arrow1"></div>
                            <div class="foc-ewm-arrow2"></div>
                            {{--<img src="../assets/images/bank/zgyh.jpg" alt="">--}}
                            {{--<img src="{!! url(Theme::get('site_config')['wechat']['wechat_pic']) !!}" alt="" width="152" height="126">--}}
                            <img src="{!! url(Theme::get('site_config')['wechat']['wechat_pic']) !!}" alt="" width="100" height="100">
                        </div>
                    </div>
                    @endif
                    @if(Theme::get('site_config')['tencent']['tencent_switch'] == 1)<div class="foc"><a class="focususqq" href="{!! Theme::get('site_config')['tencent']['tencent_url'] !!}" target="_blank"></a></div>@endif
                    @if(Theme::get('site_config')['sina']['sina_switch'] == 1)<div class="foc"><a class="focususwb" href="{!! Theme::get('site_config')['sina']['sina_url'] !!}" target="_blank"></a></div>@endif

                </div>
            </div>
        </div>
    </div>
</div>

{!! Theme::get('site_config')['statistic_code'] !!}
{!! Theme::widget('popup')->render() !!}
{{--{!! Theme::widget('statement')->render() !!}--}}
@if(Theme::get('is_IM_open') == 1)
{!! Theme::widget('im',
array('attention' => Theme::get('attention'),
'ImIp' => Theme::get('basis_config')['IM_config']['IM_ip'],
'ImPort' => Theme::get('basis_config')['IM_config']['IM_port']))->render() !!}
@endif