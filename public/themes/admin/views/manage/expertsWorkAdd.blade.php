
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">编辑仲裁专家服务履历</h3>


<form class="form-horizontal clearfix registerform" role="form" enctype="multipart/form-data"  action="/manage/expertsWorkHandle" method="post">
	{!! csrf_field() !!}
	<div class="g-backrealdetails clearfix bor-border">
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 专家姓名：</p>
			<p class="col-sm-4">
				<zz style="line-height: 34px;font-size: 20px">{!! $experts->name !!}</zz>
				{{--<span class="help-inline col-xs-12 col-sm-7"><i class="light-red ace-icon fa fa-asterisk"></i></span>--}}
			</p>
		</div>
		<input type="hidden" name="eid" value="{!! $experts->id !!}">
		@if(isset($work))
		<input type="hidden" name="id" value="{!! $work->id !!}">
		@endif
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 所在公司：</p>
			<p class="col-sm-4">
				<input @if(isset($work))value="{!! $work->company !!}"@endif type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="company">
			</p>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 图片：</p>
			<div class="col-sm-4">
				<div class="memberdiv pull-left">
					<div class="position-relative">
						<input type="file" id="id-input-file-3" name="img" />
					</div>
				</div>
			</div>
		</div>
		@if(isset($work))
			<style>
				#head_pic{
					width:20%;height:25%;
				}
			</style>
			<div class="bankAuth-bottom clearfix col-xs-12">
				<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 原头像：</p>
				<p class="col-sm-4">
					<img id="head_pic" src="{!! url($work->img) !!}" style="" onmouseleave="toSmall(this)" onmouseenter="toBig(this)" alt="原头像">
				</p>
			</div>
			<script>
                function toBig(obj){
                    $(obj).css({'width':'40%','height':'50%'});
                }
                function toSmall(obj){
                    $(obj).css({'width':'20%','height':'25%'});
                }
			</script>
		@endif
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" > 职称：</p>
			<p class="col-sm-4">
				<select name="position">
					<option value="0">请选择专家职称</option>
					@foreach($position as $v)
						<option
								@if(isset($work)&&$work->position==$v->id) selected @endif
								value="{!! $v->id !!}">{!! $v->position !!}</option>
					@endforeach
				</select>
			</p>
		</div>
		{{--<div class="bankAuth-bottom clearfix col-xs-12">--}}
			{{--<p class="col-sm-1 control-label no-padding-left" for="form-field-1" >擅长领域</p>--}}
			{{--<p class="col-sm-4" id="cate">--}}
				{{--<select name="cate[]"  id="select" onchange="changeAppendSelect(this)">--}}
					{{--<option value="0">请选择擅长的领域</option>--}}
					{{--@if(is_array($cate))--}}
						{{--@foreach($cate as $item)--}}
							{{--<option value="{!! $item->id !!}">{!! $item->name !!}</option>--}}
						{{--@endforeach--}}
					{{--@endif--}}
				{{--</select>--}}
			{{--</p>--}}
		{{--</div>--}}
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 开始时间：</p>
			<div class="col-sm-4">
				<p class="input-group input-daterange input-group-sm col-xs-10 col-sm-5">
					<input type="text" @if(isset($work))value="{!! $work->start_time !!}"@endif class="input-sm form-control hasDatepicker" name="start_time">
					<span class="input-group-addon">
							<i class="ace-icon fa fa-calendar"></i>
					</span>
				</p>
			</div>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 结束时间：</p>
			<div class="col-sm-4">
				<p class="input-group input-daterange input-group-sm col-xs-10 col-sm-5">
					<input type="text" @if(isset($work))value="{!! $work->end_time !!}"@endif class="input-sm form-control hasDatepicker" name="end_time">
					<span class="input-group-addon">
						<i class="ace-icon fa fa-calendar"></i>
					</span>
				</p>
				<span class="help-inline col-xs-12 col-sm-7"><i class="light-red ace-icon fa fa-asterisk"></i>（提示：如果结束时间至今，则填写“至今”）</span>
			</div>
		</div>
		<div class="bankAuth-bottom clearfix col-xs-12">
			<p class="col-sm-1 control-label no-padding-left" for="form-field-1"> 工作职责：</p>
			<p class="col-sm-4">
				<textarea type="text" id="form-field-1"  class="col-xs-10 col-sm-5" name="work">@if(isset($work)){!! $work->work !!}@endif</textarea>
			</p>
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
    {{--function changeAppendSelect(act){--}}
        {{--if($(act).val()!=='0'){--}}
            {{--if($(act).next('select').length==0) {--}}
                {{--var str = '<select name="cate[]" onchange="changeAppendSelect(this)"> <option value="0" class="valueChange">请选择</option>@foreach($cate as $v)<option value="{{$v->id}}">{{$v->name}}</option> @endforeach</select>';--}}
                {{--$(act).children('option.valueChange').html('移除');--}}
                {{--$(act).after(str);--}}
            {{--}--}}
        {{--}else{--}}
            {{--$(act).remove();--}}
        {{--}--}}
        {{--unsetOtherAct(1);--}}
    {{--}--}}
    {{--//获取所有活动选项--}}
	{{--@if(is_array($act_json))--}}
    {{--var act=new Array();--}}
    {{--var actArr=new Array();--}}
	{{--@foreach($act_json as $k=>$v)--}}
    {{--act.push('{{$k}}');--}}
    {{--actArr['{{$k}}']='{{$v}}';--}}
	{{--@endforeach--}}
    {{--//获取已经存在的活动选项--}}
    {{--function getAct(){--}}
        {{--var arr=new Array();--}}
        {{--$("#cate select").each(function(){--}}
            {{--if($(this).val()!=='0') {--}}
                {{--arr.push($(this).val());--}}
            {{--}--}}
        {{--});--}}
        {{--return arr;--}}
    {{--}--}}
    {{--//删除已经存在的活动选项--}}
    {{--function unsetOtherAct(status){--}}
        {{--$("#cate select").each(function(){--}}
            {{--var arr=getAct();--}}
            {{--if($(this).val()!=='0') {--}}
                {{--arr.splice($.inArray($(this).val(), arr), 1);--}}
            {{--}--}}
            {{--var selectAct=new Array();--}}
            {{--$(this).children().each(function (k, v) {--}}
                {{--if ($.inArray($(v).val(),arr)!==-1) {--}}
                    {{--$(v).remove();--}}
                {{--}else{--}}
                    {{--selectAct.push($(v).val());--}}
                {{--}--}}
            {{--});--}}
            {{--if(status==1) {--}}
                {{--for (var i = 0; i < act.length; i++) {--}}
                    {{--if ($.inArray(act[i], selectAct) == -1&&$.inArray(act[i], arr) == -1) {--}}
                        {{--var index = act[i];--}}
                        {{--var str = '<option value="' + act[i] + '" >' + actArr[index] + '</option>';--}}
                        {{--$(this).append(str);--}}
                    {{--}--}}
                {{--}--}}
            {{--}--}}
        {{--});--}}
    {{--}--}}
	{{--@endif--}}
//unsetOtherAct(0);
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