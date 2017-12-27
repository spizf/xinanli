<script src="/js/jquery-1.9.1.min.js"></script>
    <div class="g-taskposition col-xs-12 col-left">
            您的位置：首页 > 任务大厅
    </div>

    <div class="col-xs-12 hidden-xs col-left">
        <div class="poster">
            <div data-target="#step-container" class="row-fluid " id="fuelux-wizard">
                <ul class="wizard-steps poster-time">
                    <li class="active text-left" data-target="#step1">
                        <span class="title h6 p-space">需求描述</span>
                        <span class="step">1</span>
                    </li>
                    <li data-target="#step2">
                        <span class="title h6 p-space">交易模式</span>
                        <span class="step">2</span>
                    </li>
                    <li data-target="#step3">
                        <span class="title h6 p-space">确认需求，发布</span>
                        <span class="step">3</span>
                    </li>
                    <li data-target="#step4 text-right">
                        <span class="title h6 p-space">等待审核</span>
                        <span class="step">4</span>
                    </li>
                </ul>
            </div>
        </div><!--/时间轴-->
    </div>
            <div class="col-lg-9 list-l col-left">
                <form action="/task/createTask" method="post" id="form" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="task-r">
                        <ul class="mg-margin">
                            <li>
                                <a class="task-tit" href="javascript:;" data-toggle="collapse"
                                   data-target="#demo">
                                    <span class="ace-icon fa fa-angle-double-down"
                                          data-icon-hide="ace-icon fa fa-angle-double-down"
                                          data-icon-show="ace-icon fa fa fa-angle-double-right">

                                    </span> 描述需求
                                </a>
                                <div id="demo" class="collapse in">
                                    <div class="clearfix  Validform-wysiwyg-editor">
                                        <label for="name" class="phone text-size14">项目名称：</label>
                                        <input type="text" name="title" value="{!! old('title') !!}"
                                               id="form-input-readonly"
                                               placeholder="请输入您的项目名称"
                                               datatype="*" nullmsg="请填写项目名称！" >
                                        @if($errors->first('title'))
                                            <p class="Validform_checktip Validform_wrong">
                                                {!! $errors->first('title') !!}
                                            </p>
                                        @endif
                                        <span class="Validform_checktip">
                                        </span>
                                    </div>
                                    <div class="task-bar">
                                        <label for="name" class="phone text-size14">地区：</label>
                                        <span id="area_select">
                                            <select name="province" class="selectwd" onchange="checkprovince(this)">
                                                <option>请选择省份</option>
                                                @foreach($province as $v)
                                                    <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <select name="city" id="province_check" onchange="checkcity(this)" class="selectwd">
                                                <option>请选择城市</option>
                                                @foreach($city as $v)
                                                    <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <select name="area" id="area_check" onchange="arealimit(this)" class="selectwd">
                                                <option>请选择地区</option>
                                                @foreach($area as $v)
                                                    <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </span>
                                        <span>
                                            @foreach($area as $v)
                                                <div style="display:none;" id="province-{{ $v['id'] }}">
                                                    @if(!empty($v['children_area']))
                                                        @foreach($v['children_area'] as $value)
                                                            <option value={{ $v['id'] }}>
                                                                {{ $value['name'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        </span>
                                        <input type="hidden" name="area" id="region-limit" value="0" />
                                    </div>
                                    <div class="task-bar">
                                        <label for="name" class="phone text-size14">需求类别：</label>
                                        <span id="area_select">
                                            <select name="cate_id" class="selectwd" id="task_category">
                                                <option>请选择服务类型</option>
                                                @foreach($category_all as $v)
                                                    <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <select name="industry[]" id="field" class="selectwd">
                                                <option>请选择行业</option>
                                                @foreach($field as $v)
                                                    <option value="{{$v->id}}">{{$v->name}}</option>
                                                @endforeach
                                            </select>
                                            <select name="industry[]" id="field1" class="selectwd">
                                                <option>请选择子行业</option>
                                            </select>
                                            <script>
                                             $('#field').change(function(){
                                                 console.info('aa');
                                                 if(!!this.value){
                                                     $.ajax({
                                                         type:'GET',
                                                         url:"/getField/"+this.value,
                                                         success:function(data){
                                                             var str='<option>请选择子行业</option>';
                                                             for(var i=0;i<data.length;i++) {
                                                                 str += "<option value='" + data[i].id + "'>"+data[i].name+"</option>";
                                                             }
                                                             $('#field1').html(str);
                                                         }
                                                     });
                                                 }else{
                                                     layer.msg('请先选择一个行业类型！');
                                                 }
                                             });

                                        </script>
                                        </span>
                                    </div>
                                    <div class="clearfix  Validform-wysiwyg-editor" id="editor_validform">
                                        <label for="name" class="phone text-size14">生产产品：</label>
                                        <input type="text" name="description" id="discription-edit"
                                        datatype="*1-5000" nullmsg="生产产品不能为空！"
                                        errormsg="字数超过限制" >
                                        @if($errors->first('description'))
                                        <p class="Validform_checktip Validform_wrong">
                                            {!! $errors->first('description') !!}
                                        </p>
                                        @endif
                                        <span class="Validform_checktip">
                                        </span>
                                    </div>

                                    <div class="clearfix  Validform-wysiwyg-editor" >
                                        <label for="name" class="phone text-size14">年规模/产量：</label>
                                        <input type="text" name="productNum" id="form-input-readonly"
                                               datatype="*" nullmsg="年规模/产量不能为空">
                                        @if($errors->first('productNum'))
                                            <p class="Validform_checktip Validform_wrong">
                                                {!! $errors->first('productNum') !!}
                                            </p>
                                        @endif
                                        <span class="Validform_checktip">
                                        </span>
                                    </div>
                                    <div class="clearfix  Validform-wysiwyg-editor" >
                                        <label for="name" class="phone text-size14">联系人：</label>
                                        <input type="text" id="contacts"  name="contacts" value="{!! old('contacts') !!}"
                                               placeholder="请输入联系人姓名" datatype="zh2-4"
                                               nullmsg="请输入联系人姓名！" errormsg="请输入2-4位中文字符">
                                        <label class="Validform_checktip task-checkip task-checkip-wrong">

                                        </label>
                                        @if($errors->first('phone'))
                                            <span class="Validform_checktip task-checkip task-checkip-wrong">
                                                    {!! $errors->first('phone') !!}
                                                </span>
                                        @endif
                                    </div>
                                    <div class="clearfix  Validform-wysiwyg-editor">
                                        <label for="name" class="phone text-size14">手机号：</label>
                                        <input type="text"   name="phone" value="{!! old('phone') !!}"
                                               placeholder="请输入手机号" datatype="m"
                                               nullmsg="请填写手机号！" errormsg="手机号错误！">
                                        <label class="Validform_checktip task-checkip task-checkip-wrong">

                                        </label>
                                        @if($errors->first('phone'))
                                            <span class="Validform_checktip task-checkip task-checkip-wrong">
                                                    {!! $errors->first('phone') !!}
                                                </span>
                                        @endif
                                    </div>
                                    <div class="clearfix  Validform-wysiwyg-editor" >
                                        <label for="name" class="phone text-size14">任务详情：</label>

                                        <textarea name="task_detail"  class="col-xs-12" placeholder="请输入您的项目任务详情"
                                                  datatype="*" nullmsg="请填写任务详情！"  rows="5"></textarea>
                                        @if($errors->first('task_detail'))
                                            <p class="Validform_checktip Validform_wrong">
                                                {!! $errors->first('task_detail') !!}
                                            </p>
                                        @endif
                                        <span class="Validform_checktip">
                                        </span>
                                    </div>
                                    {{--<div class="annex">
                                        <!--文件上传-->
                                        <div  class="dropzone clea>fix" id="dropzone"
                                              url="{{ URL('task/fileUpload')}}"
                                              deleteurl="{{ URL('task/fileDelet') }}">
                                            <div class="fallback">
                                                <input name="file" type="file" multiple="" />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="file_update"></div>--}}
                                </div>
                            </li>
                            <li>
                                {{--<a class="task-tit" href="javascript:;" data-toggle="collapse"
                                   data-target="#demol">
                                    <span class="ace-icon fa fa-angle-double-down"
                                          data-icon-hide="ace-icon fa fa-angle-double-down"
                                          data-icon-show="ace-icon fa fa fa-angle-double-right">

                                    </span> 交易模式
                                </a>--}}
                                <div id="demol" class="panel-group">
                                    @forelse($rewardModel as $k => $type_v)
                                    <div class="panel panel-default">
                                        @if($type_v['alias'] == 'zhaobiao')
                                        {{--<div class="checkbox panel-heading">
                                            <label class="z-check z-check-validform"
                                                   data-toggle="collapse"
                                                   data-parent="#demol" href="#ck1">
                                                <input  type="radio" class="ace" name="type_id"
                                                        {{ (!empty(old('zhaobiao')))?'checked':'' }}
                                                        datatype="*" nullmsg="请选择交易模式"
                                                        value="{{$type_v['id']}}"/>
                                                <span class="lbl text-blod text-size16 pull-left">
                                                    招标模式
                                                </span>
                                            </label>
                                            <div class=" mission-task">
                                                <p class="text-size14 cor-gray89">适合建筑设计、网站开发、软件开发、装修设计等周期较长、工作较复杂的项目类需求，需要找人单独服务。发布需求后，服务商会对你的需求报价，你选择一个合适的一对一服务</p>
                                            </div>
                                        </div>--}}

                                        {{--<div class="mission-task panel-collapse collapse
                                            {{ (!empty(old('zhaobiao')))?'in':'' }}" id="ck1">--}}
                                            <input type="hidden" name="type_id" value="{{$type_v['id']}}">
                                            <div class="mission-ck z-check-validform-ck">
                                                <div class="checkbox">
                                                    <label class="checkbox-inline">
                                                        设置报名时间
                                                    </label>
                                                </div>
                                                <div class=" input-group" style="width:60%;margin-left:20px;">
                                                    <span class="input-group-addon date-icon" >
                                                        <i class="fa fa-calendar bigger-110"></i>
                                                    </span>
                                                    <div>
                                                        <input type="text" class="input-sm form-control "
                                                               id="datepiker-begin1"
                                                               onchange="beginAt($(this))"
                                   value="{!! (old('begin_at'))?old('begin_at'):date('Y年m月d日',time()) !!}"
                                                               placeholder="开始时间" >
                                                    </div>
                                                    <span class="input-group-addon  date-icon ">
                                                        <i class="fa fa-exchange"></i>
                                                    </span>
                                                    <div>
                                                        <input type="text" class="input-sm form-control "
                                                               id="datepiker-deadline1"
                                                               onchange="deadline($(this))"
                                                               value="{!! old('delivery_deadline') !!}"
                                                               placeholder="截止时间">
                                                    </div>
                                                </div>
                                            @if($errors->first('start')|| $errors->first('delivery_deadline') )
                                                    <p class="Validform_checktip Validform_wrong">
                                                        {!! $errors->first('begin_at') !!}
                                                        {!! $errors->first('delivery_deadline') !!}
                                                    </p>
                                            @endif
                                                <input name="begin_at{{$type_v['alias']}}" type="hidden" id="begin_at1" datatype="*"
                                                       nullmsg="请填写开始时间！" class="begin_at"
                                   value="{!! (old('begin_at'))?old('begin_at'):date('Y年m月d日',time()) !!}"/>
                                                <input name="delivery_deadline{{$type_v['alias']}}" type="hidden"
                                                       id="delivery_deadline1" class="delivery_deadline"
                                                       ajaxurl="/task/checkDeadlineByBid"
                                                       datatype="*" nullmsg="请填写截止时间！"
                                                       value="{!! old('delivery_deadline') !!}" />
                                                <span class="time_check validform_checktip  Validform_wrong"
                                                      style="width:90%;margin-left:20px;display:none">

                                                </span>
                                                <input type="hidden" name="worker_num{{$type_v['alias']}}" value="1">
                                                <input type="hidden" name="bounty{{$type_v['alias']}}" value="">
                                                {{--<div class="checkbox ">
                                                    <label class="checkbox-inline">
                                                        您的预算
                                                    </label>
                                                </div>
                                                <div>
                                                    <input type="number" name="bounty{{$type_v['alias']}}" class="mis-txt"
                                                           placeholder="若无明确预算，可不填">
                                                </div>
                                                <ul class="clearfix checkbox process">
                                                    <li class="pull-left">
                                                        <span></span>
                                                        <p>发布需求</p>
                                                    </li>
                                                    <li class="pull-left">
                                                        <span></span>
                                                        <p>服务商报价</p>
                                                    </li>
                                                    <li class="pull-left">
                                                        <span></span>
                                                        <p>选择服务商并托管赏金</p>
                                                    </li>
                                                    <li class="pull-left">
                                                        <span></span>
                                                        <p>服务商工作</p>
                                                    </li>
                                                    <li class="pull-left">
                                                        <span></span>
                                                        <p>验收付款</p>
                                                    </li>
                                                    <li class="pull-left">
                                                        <span></span>
                                                        <p>评价</p>
                                                    </li>
                                                </ul>--}}
                                            </div>
                                        {{--</div>--}}
                                        @endif
                                    </div>
                                    @empty
                                    @endforelse
                                </div>
                            </li>
                            {{--<li>
                                <a class="task-tit" href="javascript:;" data-toggle="collapse" data-target="#demor">
                                    <span class="ace-icon fa fa-angle-double-down fa-angle-double-{{ (!empty(old('product')))?'down':'right' }}" data-icon-hide="ace-icon fa fa-angle-double-down" data-icon-show="ace-icon fa fa fa-angle-double-right"></span> 增值服务
                                </a>
                                <div id="demor" class="collapse in {{ (!empty(old('product')))?'in':'' }}">
                                    <ul class="vat">
                                        @foreach($service as $v)
                                            <li class="clearfix">
                                                <div class="pull-left">
                                                    <div class="checkbox pull-left">
                                                        <label>
                                                            <input type="checkbox" name="product[]" {{ (!empty(old('product')) && in_array($v['id'],old('product')))?'checked':'' }} class="taskservice" price="{{ $v['price'] }}" value={{ $v['id'] }}><span>{{ substr($v['title'],3,3) }}</span>
                                                        </label>
                                                    </div>
                                                    <div class="pull-left">
                                                        <p class="text-size14 cor-gray51">{{ $v['title'] }}</p>
                                                        <p class="p-space cor-gray89">
                                                            {{ $v['description'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="pull-right vat-txt">
                                                    ￥{{ $v['price'] }}
                                                </div>
                                            </li>
                                        @endforeach
                                        <li class="clearfix">
                                            <div class="checkbox pull-left">
                                                <label>
                                                    <input type="checkbox" id="taskservice_all"><span class="vat-check" >全选</span>
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>--}}
                        </ul>
                    </div>
                    <div class="task-bt">
                        <h3><strong>结算清单</strong></h3>
                        <p class="text-size14">托管赏金：<span>￥<span id="bounty_money">0</span></span></p>
                        <div style="display:none;" id="service-box">
                            <p>增值服务：</p>
                            @foreach($service as $v)
                                <p class="bt-pd" style="display:none;" id="service-{{ $v['id'] }}">
                                    {{ $v['title'] }}：<span>￥{{ $v['price'] }}
                                    </span>
                                </p>
                            @endforeach
                        </div>
                        <p class="text-size14">应付总额 <span>￥<span id="total-price">0</span></span></p>
                        <div class="clearfix text-size12">
                            <label class="inline task-validform-right">
                                @if(!empty($agree))
                                <input type="checkbox" class="ace" name="agree" checked="checked" datatype="*"
                                       nullmsg="请先阅读并同意">
                                <span class="lbl text-muted">&nbsp;&nbsp;&nbsp;我已阅读并同意
                                    <a target="_blank" class="text-under" href="/bre/agree/task_publish">
                                        《{!! $agree->name !!}》
                                    </a>
                                </span>
                                @endif
                            </label>
                        </div>
                        <input type='hidden' name="slutype" value="1" id="slutype"/>
                        <button class="btn btn-primary btn-blue bor-radius2 btn-big3 preservation" onclick="sluSub(1)">
                            提交
                        </button>
                        {{--<a href="javascript:sluSub(2);"  class="text-size14 text-under preview">预览任务</a>--}}
                        {{--<a href="javascript:sluSub(3);" class="text-size14 text-under not_released">暂不发布</a>--}}
                    </div>
                </form>
            </div>
            <div class="col-lg-3 task-l hidden-xs hidden-md hidden-sm col-left">
                <div class="taskside">
                    <p class="text-center text-size14 cor-gray51 mg-bottom20">遇到问题，联系客服免费帮您解决</p>
                    <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin={{ $qq }}&site=qq&menu=yes"
                       class="btn btn-block btn-primary bor-radius2 text-size14 btn-blue">联系在线客服</a>
                    <div class="space"></div>
                    <div class="iss-ico1">
                        <p class="cor-gray51 mg-margin">全国免长途电话：</p>
                        <p class="text-size20 cor-gray51">{{ $phone }}</p>
                    </div>
                    <div class="iss-ico2">
                        <p class="cor-gray51 mg-margin">企业QQ：</p>
                        <p class="text-size20 cor-gray51">{{ $qq }}</p>
                    </div>
                </div>
                <div class="taskside1">
                    @if(count($ad))
                        <a href="{!! $ad[0]['ad_url'] !!}">
                            <img src="{!! URL($ad[0]['ad_file']) !!}"
                                 alt="" class="img-responsive" width="100%">
                        </a>
                    @endif
                </div>
            </div>

    {!! Theme::asset()->container('custom-css')->usepath()->add('issuetask','css/taskbar/issuetask.css') !!}
    {{--{!! Theme::asset()->container('specific-css')->usepath()->add('issuetask','plugins/ace/css/dropzone.css') !!}--}}
    {!! Theme::asset()->container('specific-css')->usepath()->add('chossen','css/ace/chosen.css') !!}
    {{--{!! Theme::asset()->container('custom-js')->usepath()->add('ace-extra', 'plugins/ace/js/ace/ace-extra.min.js') !!}--}}

    {{--{!! Theme::asset()->container('custom-js')->usepath()->add('dropzone','plugins/ace/js/dropzone.min.js') !!}--}}
    {!! Theme::asset()->container('custom-js')->usepath()->add('chosen', 'plugins/ace/js/chosen.jquery.min.js') !!}
    {!! Theme::widget('ueditor')->render() !!}
    {{--{!! Theme::widget('editor',['plugins'=>CommonClass::getEditorInit(['insertImage'])])->render() !!}--}}
    {!! Theme::widget('datepicker')->render() !!}
    {{--{!! Theme::widget('fileUpload')->render() !!}--}}
    {!! Theme::asset()->container('custom-js')->usepath()->add('task', 'js/doc/task.js') !!}
    <!--日历 有可能是手机端的，暂时不能删除-->
    {{--{!! Theme::asset()->container('custom-js')->usepath()->add('custom', 'plugins/ace/js/jquery-ui.custom.min.js') !!}--}}
    {{--{!! Theme::asset()->container('custom-js')->usepath()->add('touch-punch', 'plugins/ace/js/jquery.ui.touch-punch.min.js') !!}--}}

    {!! Theme::asset()->container('specific-css')->usepath()->add('validform-css','plugins/jquery/validform/css/style.css') !!}
    {!! Theme::asset()->container('specific-js')->usepath()->add('validform-js','plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}