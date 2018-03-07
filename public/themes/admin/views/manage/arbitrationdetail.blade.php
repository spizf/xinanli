<h3 class="header smaller lighter blue mg-top12 mg-bottom20">仲裁详情</h3>


<div class="widget-header widget-header-flat widget-header-small">
    <h5 class="widget-title">
        <i class="ace-icon fa fa-check-square"></i>
        选择仲裁专家
    </h5>
</div>
<form action="/manage/arbitrationSubmit" method="post">
    {!! csrf_field() !!}
    <input type="hidden" name="id" value="{{$list->id}}">
    <input type="hidden" name="task_id" value="{{$list->task_id}}">
    <input type="hidden" name="num" value="{{$list->num}}">
<div class="zongjian">
    {{--{{dd($list)}}--}}
    @if(!empty($list))
        @foreach($list->ex_name_zh as $k=>$item)
            <div class="zongjianle" style="width: 226px;height:350px;margin-right: 10px;" data="20">
                <div class="touxiang zongjianle2">
                    <img src="{!! url($item->head_img) !!}">
                </div>
                <h3>{!! $item->name !!}</h3>
                <h4>{{\DB::table('position')->whereId($item->position)->first()->position}}</h4>
                <div class="xinxi zongjianle2">
                    <dl>
                        <dd>{!! $item->year !!}年工作经验</dd>
                        <dd>{{\DB::table('district')->whereId(explode('-',$item->addr)[0])->first()->name}}</dd>
                        @if(isset(explode('-',$item->addr)[1]) && !empty(explode('-',$item->addr)[1]))
                        <dd>{{\DB::table('district')->whereId(explode('-',$item->addr)[1])->first()->name}}</dd>
                        @endif
                    </dl>
                </div>
                <div class="shanchang zongjianle2">
                    <dd>平台身份:组长</dd>
                    <dd>联系方式:{!! $item->tell !!}</dd>
                </div>
                <div class="dubox zongjianle2" style="margin: 24px 8px;">
                    <input type="checkbox" @if(in_array($item->id,explode('-',$list->result_experts))) checked @endif name="result_experts[]" value="{{$item->id}}"><span class="label label-primary">确定合作</span>
                </div>
                <div class="liji">
                </div>
            </div>
        @endforeach
        @foreach($list->ex_name_z as $k=>$item)
            <div class="zongjianle" style="width: 226px;height:350px;margin-right: 10px;" data="20">
                <div class="touxiang zongjianle2">
                    <img src="{!! url($item->head_img) !!}">
                </div>
                <h3>{!! $item->name !!}</h3>
                <h4>{{\DB::table('position')->whereId($item->position)->first()->position}}</h4>
                <div class="xinxi zongjianle2">
                    <dl>
                        <dd>{!! $item->year !!}年工作经验</dd>
                        <dd>{{\DB::table('district')->whereId(explode('-',$item->addr)[0])->first()->name}}</dd>
                        @if(isset(explode('-',$item->addr)[1]) && !empty(explode('-',$item->addr)[1]))
                            <dd>{{\DB::table('district')->whereId(explode('-',$item->addr)[1])->first()->name}}</dd>
                        @endif
                    </dl>
                </div>
                <div class="shanchang zongjianle2">
                    <dd>平台身份:组员</dd>
                    <dd>联系方式:{!! $item->tell !!}</dd>
                </div>
                <div class="dubox zongjianle2" style="margin: 24px 8px;">
                    <input type="checkbox" @if(in_array($item->id,explode('-',$list->result_experts))) checked @endif name="result_experts[]"  value="{{$item->id}}"><span class="label label-primary">确定合作</span>
                </div>
                <div class="liji">
                </div>
            </div>
        @endforeach
    @else
        暂无推荐专家！
    @endif
</div>
<div class="widget-header widget-header-flat widget-header-small">
    <h5 class="widget-title">
        <i class="ace-icon fa fa-book"></i>
        仲裁原因
    </h5>
</div>
<textarea name="reason" id="res" style="width: 60%;" rows="5">{{$list->user_zc->reason}}</textarea>
<div class="widget-header widget-header-flat widget-header-small">
    <h5 class="widget-title">
        <i class="ace-icon fa fa-check-square"></i>
        选择组长
    </h5>
    <select name="headman" id="">
        @foreach($list->ex_name_zh as $k=>$item)
            @if($item->id==$list->headman)
                <option value="{!! $item->id !!}" selected="selected">{!! $item->name !!}</option>
            @else
                <option value="{!! $item->id !!}">{!! $item->name !!}</option>
            @endif
        @endforeach
        @foreach($list->ex_name_z as $k=>$item)
            @if($item->id==$list->headman)
                <option value="{!! $item->id !!}" selected="selected">{!! $item->name !!}</option>
            @else
                <option value="{!! $item->id !!}">{!! $item->name !!}</option>
            @endif
        @endforeach
    </select>
</div>
<br>
<button type="submit" class="btn btn-primary">保存</button>
</form>
{!! Theme::asset()->container('custom-css')->usePath()->add('newcss', 'css/backstage/newcss.css') !!}