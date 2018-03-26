<div class="fd_footer">
    <div class="wrapper">
        <div class="fdfotte-left">
            <div class="fd-address">
                @if(!empty(Theme::get('article_cate')))
                    @foreach(Theme::get('article_cate') as $item)
                        <a target="_blank" href="/article/aboutUs/{!! $item['id'] !!}">{!! $item['cate_name'] !!}</a>
                        <span></span>
                    @endforeach
                @endif
            </div>
            <p>公司名称：{!! Theme::get('site_config')['company_name'] !!} &nbsp;&nbsp;地址：{!! Theme::get('site_config')['company_address'] !!}</p>
            <p>{!! Theme::get('site_config')['copyright'] !!}{!! Theme::get('site_config')['record_number'] !!} | 安环家荣誉出品
            </p>
            <div class="zhengjian">
                <a id='___szfw_logo___' href='https://credit.szfw.org/CX02272018016655810199.html' target='_blank'><img src='http://icon.szfw.org/cert.png' border='0' /></a>
                <a href=" http://www.315online.com.cn/member/315180004.html" target="_blank" style="margin-left: 5px;"><img  src="/img/cert.png" height="41" width="96"  border="0"></a>
                <a href="https://ss.knet.cn/verifyseal.dll?sn=e180315110105723634may000000&pa=111332" tabindex="-1" id="urlknet" style="margin-left: 5px;" target="_blank">
                    <img alt="&#x53EF;&#x4FE1;&#x7F51;&#x7AD9;" name="KNET_seal" border="true" src="/img/ss.png"  />
                </a>
               </div>
        </div>
        <div class="fdfotte-lt2">
            <div class="fd-contacthd">联系方式</div>
            <p>服务热线：{!! Theme::get('site_config')['phone'] !!}</p>
            <p>Email：{!! Theme::get('site_config')['Email'] !!}</p>
        </div>
        <div class="fdfotte-lt3">
            <div class="fd-contacthd2">关注我们</div>
            <div class="lianxi">
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
                @if(Theme::get('site_config')['tencent']['tencent_switch'] == 1)
                    <div class="foc"><a class="focususqq" href="{!! Theme::get('site_config')['tencent']['tencent_url'] !!}" target="_blank"></a></div>
                @endif
                @if(Theme::get('site_config')['sina']['sina_switch'] == 1)
                        <div class="foc"><a class="focususwb" href="{!! Theme::get('site_config')['sina']['sina_url'] !!}" target="_blank"></a></div>
                @endif
            </div>
        </div>
    </div>

</div>

{!! Theme::get('site_config')['statistic_code'] !!}
{!! Theme::widget('popup')->render() !!}
{{--{!! Theme::widget('statement')->render() !!}--}}
{{--
@if(Theme::get('is_IM_open') == 1)
{!! Theme::widget('im',
array('attention' => Theme::get('attention'),
'ImIp' => Theme::get('basis_config')['IM_config']['IM_ip'],
'ImPort' => Theme::get('basis_config')['IM_config']['IM_port']))->render() !!}
@endif--}}
