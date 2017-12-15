<div class="g-main g-main-right">
    <h4 class="text-size16 cor-blue2f u-title">企业认证</h4>

    <div class="space-20"></div>
    <div class="tab-pane g-userimgup">
        <form class="registerform" enctype="multipart/form-data" method="post"
              action="{!! url('user/realnameAuth') !!}">
            {!! csrf_field() !!}
            <div class="row profile-users" id="user-profile-2">
                <div class="col-md-12 realimg">
                    <div class="clearfix g-userimgupbor task-casehid">
                        <p class="pull-left h5 cor-gray51">企业名称</p>
                        <p class="g-userimgupinp g-userimgupbor-validform">
                            <input type="text" name="realname" class="inputxt input-large" datatype="*" nullmsg="请填写企业名称！" />&nbsp;&nbsp;&nbsp;
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
                            <input class="inputxt Validform_error input-large" name="card_number" datatype="*" nullmsg="请填写营业执照号！"
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
                    <div class="clearfix g-userimgupbor" data-placement="right" href="#">
                        <p class="pull-left h5 cor-gray51"><span>上传营业执照照片</span></p>

                        <div class="memberdiv pull-left">
                            <div class="position-relative">
                                <input name="validation_img" type="file" id="id-input-file-5"/>
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
                </div>
            </div>
            <div class="text-center">
                <div class="space-20"></div>
                <button id="btn_sub" type="button" class="u-userimgupbtn btn-imp">立即认证</button>
                <div class="space-10"></div>
            </div>
        </form>
    </div>
</div>

{!! Theme::asset()->container('specific-css')->usePath()->add('webui-css', 'plugins/jquery/css/jquery.webui-popover.min.css') !!}
{!! Theme::asset()->container('specific-css')->usePath()->add('validform-css', 'plugins/jquery/validform/css/style.css') !!}
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
{!! Theme::widget('avatar')->render() !!}
{{--{!! Theme::asset()->container('specific-js')->usePath()->add('common-js', 'js/common.js') !!}--}}