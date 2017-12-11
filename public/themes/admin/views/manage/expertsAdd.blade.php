
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">添加仲裁专家</h3>


<form class="form-horizontal clearfix registerform" role="form" enctype="multipart/form-data"  action="expertsAddHandle" method="post">
	{!! csrf_field() !!}
	<div class="g-backrealdetails clearfix bor-border">
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 专家姓名：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="name" datatype="*2-4" nullmsg="请输入专家姓名" errormsg="用户名长度为2到4位字符">
				{{--<span class="help-inline col-xs-12 col-sm-7"><i class="light-red ace-icon fa fa-asterisk"></i></span>--}}
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 专家头像：</p>
			<div class="col-sm-4">
				<div class="memberdiv pull-left">
					<div class="position-relative">
						<input type="file" id="id-input-file-3" name="head_img" />
					</div>
				</div>
			</div>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" > 职称：</p>
			<p class="col-sm-4">
				<select name="position">
					<option value="0">请选择专家职称</option>
					@foreach($position as $v)
						<option value="{!! $v->id !!}">{!! $v->position !!}</option>
					@endforeach
				</select>
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left"> 职级：</p>
			<p class="col-sm-4">
				<select name="position_level">
					<option value="0">请选择专家职级</option>
					@foreach($position_level as $v)
						<option value="{!! $v->id !!}">{!! $v->position_level !!}</option>
					@endforeach
				</select>
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 所属区域：</p>
			<p class="col-sm-4" id="addr">
				<select name="addr[]" id="addr_1">
					<option value="0">请选择地区</option>
					@foreach($addr as $item)
						<option value="{!! $item->id !!}">{!! $item->name !!}</option>
					@endforeach
				</select>
			</p>
		</div>
		<script>
			$('#addr_1').change(function(){
			    if($(this).val()!=0){
			        var addr_1=$(this).val();
                    $.ajax({
                        type: 'POST',
                        url: '{!! url('manage/ajaxGetAddr') !!}',
                        data: {'id':addr_1,'_token': '<?php echo csrf_token(); ?>'},
                        success: function(addr){
                            $("#addr_2").remove();
                            $("#addr_3").remove();
                            if(addr){
							    var arr=JSON.parse(addr);
							    var str='<select onchange="changeAddr2(this)" name="addr[]" id="addr_2"><option value="0">请选择地区</option>';
                                for(var i in arr) {
                                    str+='<option value="'+arr[i].id+'">'+arr[i].name+'</option>';
                                }
                                str+='</select>';
                                $('#addr').append(str);
							}
						}
                    });
				}
			});

            function changeAddr2(obj) {
                if ($(obj).val() != 0) {
                    var addr_2 = $(obj).val();
                    $.ajax({
                        type: 'POST',
                        url: '{!! url('manage/ajaxGetAddr') !!}',
                        data: {'id': addr_2, '_token': '<?php echo csrf_token(); ?>'},
                        success: function (addr) {
                            $("#addr_3").remove();
                            if (addr) {
                                var arr = JSON.parse(addr);
                                var str = '<select name="addr[]" id="addr_3"><option value="0">请选择地区</option>';
                                for (var i in arr) {
                                    str += '<option value="' + arr[i].id + '">' + arr[i].name + '</option>';
                                }
                                str += '</select>';
                                $('#addr').append(str);
                            }
                        }
                    });
                }
            }
		</script>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 从业年限：</p>

			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="year">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1" >擅长领域</p>
			<p class="col-sm-4" id="cate">
				<select name="cate[]"  id="select" onchange="changeAppendSelect(this)">
					<option value="0">请选择擅长的领域</option>
					@if(is_array($cate))
						@foreach($cate as $item)
							<option value="{!! $item->id !!}">{!! $item->name !!}</option>
						@endforeach
					@endif
				</select>
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 专家等级：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="level">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 推荐指数：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="recommend">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 满意度：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="satisfaction">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 咨询量：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="ask_num">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 服务用户数：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="service_num">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 平均响应时间：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="do_time">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 毕业院校：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="un_school">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 大学专业：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="un_learn">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 是否显示所属机构：</p>
			<p class="col-sm-4">
				<input type="radio"  name="is_show_jigou" value=1>是
				<input type="radio"  name="is_show_jigou"  checked value=0/>否
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 毕业时间：</p>
			<div class="col-sm-4">
				<p class="input-group input-daterange input-group-sm col-xs-10 col-sm-5">
					<input type="text" class="input-sm form-control hasDatepicker" name="un_time">
					<span class="input-group-addon">
							<i class="ace-icon fa fa-calendar"></i>
					</span>
				</p>
			</div>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 所持证件：</p>
			<p class="col-sm-4">
				<input type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="un_certificate">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 证书有效时间：</p>
			<div class="col-sm-4">
				<p class="input-group input-daterange input-group-sm col-xs-10 col-sm-5">
					<input type="text" class="input-sm form-control hasDatepicker" name="un_cer_time">
					<span class="input-group-addon">
						<i class="ace-icon fa fa-calendar"></i>
					</span>
				</p>
			</div>
		</div>
		{{--<div class="form-group text-center">
				<label class="col-sm-1 control-label no-padding-left" for="form-field-1"></label>
				<div class="col-sm-3 text-center">
					<button class="btn btn-primary" type="submit">提交</button>
				</div>
		</div>--}}
		<div class="col-xs-12">
			<div class="clearfix row bg-backf5 padding20 mg-margin12">
				<div class="col-xs-12">
					<div class="col-md-1 text-right"></div>
					<div class="col-md-10">
						<button class="btn btn-primary btn-sm" type="submit">提交</button>
					</div>
				</div>
			</div>
		</div>
		<div class="space col-xs-12"></div>
		{{--<div class="col-xs-12">--}}
			{{--<div class="col-md-1 text-right"></div>--}}
			{{--<div class="col-md-10"><a href="">上一项</a>　　<a href="">下一项</a></div>--}}
		{{--</div>--}}
		<div class="col-xs-12 space">

		</div>
	</div>
</form>
<script>
    function changeAppendSelect(act){
        if($(act).val()!=='0'){
            if($(act).next('select').length==0) {
                var str = '<select name="cate[]" onchange="changeAppendSelect(this)"> <option value="0" class="valueChange">请选择</option>@foreach($cate as $v)<option value="{{$v->id}}">{{$v->name}}</option> @endforeach</select>';
                $(act).children('option.valueChange').html('移除');
                $(act).after(str);
            }
        }else{
            $(act).remove();
        }
        unsetOtherAct(1);
    }
    //获取所有活动选项
	@if(is_array($act_json))
    var act=new Array();
    var actArr=new Array();
	@foreach($act_json as $k=>$v)
    act.push('{{$k}}');
    actArr['{{$k}}']='{{$v}}';
	@endforeach
    //获取已经存在的活动选项
    function getAct(){
        var arr=new Array();
        $("#cate select").each(function(){
            if($(this).val()!=='0') {
                arr.push($(this).val());
            }
        });
        return arr;
    }
    //删除已经存在的活动选项
    function unsetOtherAct(status){
        $("#cate select").each(function(){
            var arr=getAct();
            if($(this).val()!=='0') {
                arr.splice($.inArray($(this).val(), arr), 1);
            }
            var selectAct=new Array();
            $(this).children().each(function (k, v) {
                if ($.inArray($(v).val(),arr)!==-1) {
                    $(v).remove();
                }else{
                    selectAct.push($(v).val());
                }
            });
            if(status==1) {
                for (var i = 0; i < act.length; i++) {
                    if ($.inArray(act[i], selectAct) == -1&&$.inArray(act[i], arr) == -1) {
                        var index = act[i];
                        var str = '<option value="' + act[i] + '" >' + actArr[index] + '</option>';
                        $(this).append(str);
                    }
                }
            }
        });
    }
	@endif
unsetOtherAct(0);
</script>

{!! Theme::widget('popup')->render() !!}
{{--{!! Theme::widget('editor')->render() !!}--}}
{!! Theme::widget('ueditor')->render() !!}
{{--{!! Theme::asset()->container('custom-css')->usePath()->add('back-stage-css', 'css/backstage/backstage.css') !!}--}}
{{--{!! Theme::asset()->container('specific-css')->usePath()->add('validform-css', 'plugins/jquery/validform/css/style.css') !!}--}}
{{--{!! Theme::asset()->container('specific-js')->usePath()->add('validform-js', 'plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}--}}


{!! Theme::asset()->container('custom-css')->usePath()->add('chosen', 'plugins/ace/css/chosen.css') !!}
{!! Theme::asset()->container('custom-css')->usepath()->add('style','css/blue/style.css') !!}

{!! Theme::asset()->container('custom-css')->usepath()->add('issuetask','css/taskbar/issuetask.css') !!}
{!! Theme::asset()->container('custom-css')->usePath()->add('backstage', 'css/backstage/backstage.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('chosen','plugins/ace/js/chosen.jquery.min.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('backstage', 'js/doc/successcase.js') !!}
{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}


{!! Theme::asset()->container('specific-css')->usepath()->add('validform-css','plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('validform-js','plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}

{!! Theme::asset()->container('specific-css')->usePath()->add('datepicker-css', 'plugins/ace/css/datepicker.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('datepicker-js', 'plugins/ace/js/date-time/bootstrap-datepicker.min.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('userManage-js', 'js/userManage.js') !!}