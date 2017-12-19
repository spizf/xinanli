
<div class="g-main">
    <h4 class="text-size16 cor-blue u-title">机构认证</h4>
    <div class="space-30"></div>
    <div class="text-center g-bankhint">
        <img src="{!! Theme::asset()->url('images/sign-icon1.png') !!}"><b>恭喜，机构认证已通过！</b>
        <p class="text-size14"><a class="text-under" href="/task">去任务大厅逛逛</a></p>
    </div>
    <div class="space-20"></div>
    <div class="space-10"></div>
    <div class="cor-gray51 text-size14">您认证的机构信息</div>
    <div class="space-10"></div>
    <div class="text-size14 cor-gray51 pdl54">企业名称：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="inlineblock cor-gray97">{!! $company_info->company_name !!} <span class="u-succeedicon">已认证</span></span></div>
    <div class="space-10"></div>
    <div class="text-size14 cor-gray51 pdl54">所属行业：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="inlineblock cor-gray97">{!! $cate_name !!}</span></div>
    <div class="space-10"></div>
    <div class="text-size14 cor-gray51 pdl54">证件编号：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="inlineblock cor-gray97">{!! $company_info->card_number !!}</span></div>
    <div class="space-10"></div>
    <div class="text-size14 cor-gray51 pdl54">营业执照编号：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="inlineblock cor-gray97">{!! $company_info->business_license !!}</span></div>
    <div class="space-10"></div>
    <div class="text-size14 cor-gray51 pdl54">营业执照图片：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="inlineblock cor-gray97">已做隐私处理，不显示具体内容。</span></div>
</div>
{!! Theme::asset()->container('custom-css')->usePath()->add('realname-css', 'css/usercenter/realname/realname.css') !!}