<div class="g-main g-releasetask g-usershop">
    <h4 class="text-size16 cor-blue2f u-title">机构认证</h4>
    {{--<form method="post" action="/user/enterpriseAuth" enctype="multipart/form-data" id="enterprise">--}}
    <form method="post" action="/user/enterpriseAuth" enctype="multipart/form-data">
        <div class="space-10"></div>
        <h4 class="text-size16">认证营业执照</h4>
        <div class="space-10"></div>
        <div class="cor-orange text-size14 g-usershopi"><i class="fa fa-exclamation-circle cor-orange text-size18"></i> 用于提升客户对公司的信任度，请仔细填写相关信息，认证后不可修改</div>
        <div class="tab-pane g-userimgup">
            {!! csrf_field() !!}
            <div class="row profile-users" id="user-profile-2">
                <div class="col-md-12 realimg">
                    <div class="clearfix g-userimgupbor task-casehid">
                        <p class="pull-left h5 cor-gray51">企业名称</p>
                        <p class="g-userimgupinp g-userimgupbor-validform">
                            <input type="text" name="company_name" class="inputxt input-large" datatype="*" nullmsg="请填写企业名称！" />&nbsp;&nbsp;&nbsp;
                        </p>
                    </div>
                    <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">企业性质</p>
                        <p class="g-userimgupinp g-userimgupbor-validform">
                            <select name="enterprise_nature" id="cate_first" datatype="n" nullmsg="请选择企业性质！">
                                <option value="">-企业性质-</option>
                                @if($enterprise_nature)
                                    @foreach($enterprise_nature as $item)
                                        <option value="{!! $item['id'] !!}">{!! $item['name'] !!}</option>
                                    @endforeach
                                @endif
                            </select>
                        </p>
                    </div>
                    <div class="clearfix g-userimgupbor task-casehid g-userimguptime"><p class="pull-left h5 cor-gray51">注册时间</p>
                        <p class="input-daterange input-group g-userimgupbor-validform">
                    <span class="ass-icore ass-icore163">
                        <input type="text"  id="datepiker-begin" class="input-sm form-control" name="regist_time" value=""
                               datatype="*" nullmsg="请填写注册时间！"/>
                        <i class="fa fa-calendar ass-icoabr"></i>
                        <span class="Validform_checktip position-validform"></span>
                    </span>
                        </p>
                    </div>
                    <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">营业执照号</p>
                        <p class="g-userimgupinp g-userimgupbor-validform">
                            <input class="inputxt Validform_error input-large" name="business_license" datatype="*" nullmsg="请填写营业执照号！"
                                   type="text" placeholder="请填写您的营业执照号"  value="">&nbsp;&nbsp;&nbsp;
                        </p>
                    </div>
                    <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">注册地址</p>
                        <p class="g-userimgupinp g-userimgupbor-validform">
                            <select name="province" id="province" datatype="n" nullmsg="请选择省！">
                                <option value="">-请选择省-</option>
                                @if(isset($province) && is_array($province))
                                    @foreach($province as $k => $v)
                                        <option value="{{$v['id']}}">{{$v['name']}}</option>
                                    @endforeach
                                @endif
                            </select>
                            <select name="city" id="city" datatype="n" nullmsg="请选择市！">
                                <option value="">-请选择市-</option>
                                @if(isset($city) && is_array($city))
                                    @foreach($city as $k => $v)
                                        <option value="{{$v['id']}}" >{{$v['name']}}</option>
                                    @endforeach
                                @endif
                            </select>
                            <select name="area" id="area" datatype="n" nullmsg="请选择区！">
                                <option value="">-请选择区-</option>
                                @if(isset($area) && is_array($area))
                                    @foreach($area as $k => $v)
                                        <option value="{{$v['id']}}">{{$v['name']}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </p>
                        <p class="g-userimgupinp g-userimgupbor-validform">
                            <input class="inputxt Validform_error input-large" datatype="*" nullmsg="请填写注册地址！"
                                   type="text" placeholder="详细地址"  name="address" value="">&nbsp;&nbsp;&nbsp;

                    </div>

                </div>
            </div>
        </div>
        {{--资质认证--}}
        <div class="space-10"></div>
        <h4 class="text-size16">认证资质证书</h4>
        <div class="space-10"></div>
        <div class="g-userimgup profile-users g-usershopform">
            <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">机构名称</p>
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <input class="inputxt Validform_error input-large" datatype="*" nullmsg="请填写公司名称！" type="text"  name="organ_name" value="">&nbsp;&nbsp;&nbsp;
                </p>
            </div>
            <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">资质类型</p>
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <select name="cate_first" id="cate_first" datatype="n" nullmsg="请选择资质类型！">
                        <option value="">-资质类型-</option>
                        @if($cate_first)
                            @foreach($cate_first as $item)
                                <option value="{!! $item['id'] !!}">{!! $item['name'] !!}</option>
                            @endforeach
                        @endif
                    </select>
                </p>
            </div>
            <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">资质等级</p>
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <select name="qualification_level" id="cate_first" datatype="n" nullmsg="请选择资质等级！">
                        <option value="">-资质等级-</option>
                        @if($qualification_level)
                            @foreach($qualification_level as $item)
                                <option value="{!! $item['id'] !!}">{!! $item['name'] !!}</option>
                            @endforeach
                        @endif
                    </select>
                </p>
            </div>
            <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">评价区域</p>
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <input class="inputxt Validform_error input-large" datatype="*" nullmsg="请填写评价区域！"
                           type="text" name="evaluation_area" value="">&nbsp;&nbsp;&nbsp;
                </p>
            </div>
            <div class="clearfix g-userimgupbor task-casehid g-userimguptime"><p class="pull-left h5 cor-gray51">有效期至</p>
                <p class="input-daterange input-group g-userimgupbor-validform">
                <span class="ass-icore ass-icore163">
                    <input type="text"  id="datepiker-begin" class="input-sm form-control" name="end" value=""
                           datatype="*" nullmsg="请填写开始经营时间！"/>
                    <i class="fa fa-calendar ass-icoabr"></i>
                    <span class="Validform_checktip position-validform"></span>
                </span>

                    </span>
                </p>
            </div>
            <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">证件编号</p>
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <input class="inputxt Validform_error input-large" datatype="*" nullmsg="请填写证件编号！"
                           type="text" placeholder="请填写您的证件编号" name="card_number" value="">&nbsp;&nbsp;&nbsp;
                </p>
            </div>
            <div class="clearfix g-userimgupbor task-casehid"><p class="pull-left h5 cor-gray51">业务范围</p>
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <textarea class="inputxt Validform_error input-large"  name="website" value=""></textarea>&nbsp;
                </p>
            </div>
            <div class="clearfix g-userimgupbor" data-placement="right" href="#">
                <p class="pull-left h5 cor-gray51"><span>上传营业执照照片</span></p>

                <div class="memberdiv pull-left">
                    <div class="position-relative">
                        <input name="validation_img" type="file" id="id-input-file-3"/>
                    </div>
                </div>
                <div class="pull-left cor-gray87 hidden-xs">
                    <p>3.必须看清证件信息，且证件信息不能被遮挡</p>

                    <p>4.仅支持.jpg .bmp .png .gif 的图片格式，<b class="cor-gray51">图片大小不超过3M</b>;</p>

                    <p>5.您提供的照片信息将予以保护，不会用于其他用途。</p>

                    <p>6. <a class="cor-blue2f" data-toggle="modal" href="#g-userscimg">[示例：查看营业执照照片]</a></p>
                </div>
                <div class="modal fade" id="g-userscimg" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <img src="{!! Theme::asset()->url('images/userimgdf.png') !!}">
                            <button class="close" aria-label="Close" data-dismiss="modal"
                                    type="button"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix g-userimgupbor" data-placement="right" href="#">
                <p class="pull-left h5 cor-gray51"><span>上传企业资质照片</span></p>

                <div class="memberdiv pull-left">
                    <div class="position-relative">
                        <input name="qualification_img" type="file" id="id-input-file-5"/>
                    </div>
                </div>
                <div class="pull-left cor-gray87 hidden-xs">
                    <p>3.必须看清证件信息，且证件信息不能被遮挡</p>

                    <p>4.仅支持.jpg .bmp .png .gif 的图片格式，<b class="cor-gray51">图片大小不超过3M</b>;</p>

                    <p>5.您提供的照片信息将予以保护，不会用于其他用途。</p>

                    <p>6. <a class="cor-blue2f" data-toggle="modal" href="#g-userscimg">[示例：查看营业执照照片]</a></p>
                </div>
                <div class="modal fade" id="g-userscimg" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <img src="{!! Theme::asset()->url('images/userimgdf.png') !!}">
                            <button class="close" aria-label="Close" data-dismiss="modal"
                                    type="button"></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-20"></div>
            <button class="btn btn-primary btn-imp btn-blue g-usershopbtn">认证</button>
            <a class="text-size14 g-usershopback text-under" href="/user/shop">返回</a>
        </div>
    </form>
    <div class="space-8"></div>
</div>


{!! Theme::asset()->container('specific-css')->usePath()->add('webui-css', 'plugins/jquery/css/jquery.webui-popover.min.css') !!}
{!! Theme::asset()->container('specific-css')->usePath()->add('validform-css', 'plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('custom-css')->usePath()->add('usercenter-css', 'css/usercenter/usercenter.css') !!}
{!! Theme::asset()->container('custom-css')->usePath()->add('realname-css', 'css/usercenter/realname/realname.css') !!}
{!! Theme::asset()->container('custom-css')->usePath()->add('shop-css', 'css/usercenter/shop/shop.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('validform-js', 'plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('ace-min-js', 'plugins/ace/js/ace.min.js') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('ace-elements-js', 'plugins/ace/js/ace-elements.min.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('realname-js', 'js/realnameauth.js') !!}

{!! Theme::asset()->container('specific-css')->usepath()->add('froala_editor', 'plugins/ace/css/datepicker.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('bootstrap-datepicker','plugins/ace/js/date-time/bootstrap-datepicker.min.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('assetdetail','js/assetdetail.js') !!}

{!! Theme::asset()->container('specific-css')->usepath()->add('issuetask','plugins/ace/css/dropzone.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('dropzone','plugins/ace/js/dropzone.min.js') !!}

{!! Theme::asset()->container('custom-js')->usepath()->add('enterprise','js/doc/enterprise.js') !!}
