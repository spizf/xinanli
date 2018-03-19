   {{-- <div class="page-header">
        <h3>
              搜索
        </h3>
    </div><!-- /.page-header -->--}}
    <h3 class="header smaller lighter blue mg-top12 mg-bottom20">仲裁列表</h3>
<div class="row">
    {{--<div class="col-xs-12">--}}
        {{--<div class="clearfix  well">--}}
            {{--<div class="">--}}
                {{--<div style="float:left">--}}
                {{--<form  role="form" class="form-inline search-group" action="{!! url('manage/experts') !!}" method="get">--}}
                    {{--<div class="form-group search-list">--}}
                        {{--<label for="">姓名　　</label>--}}
                        {{--<input type="text" name="username" @if(isset($username)) value="{!! $username !!}" @endif/>--}}
                    {{--</div>--}}
                    {{--<div class="form-group">--}}
                        {{--<button class="btn btn-primary btn-sm">搜索</button>--}}
                    {{--</div>--}}
                 {{--</form>--}}
                {{--</div>--}}
                {{--<div class="form-group" style="float:right">--}}
                    {{--<a href="{!! url('manage/expertsAdd') !!}"><button class="btn btn-primary btn-sm">添加仲裁专家</button></a>--}}
                {{--</div>--}}
            {{--</div>--}}
    {{--</div>--}}
    <div>
        <table id="sample-table" class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th class="center">
                    <label class="position-relative">
                        {{--<input type="checkbox" class="ace" />--}}
                        <span class="lbl">序号</span>
                    </label>
                </th>
                <th>任务标题</th>
                <th>申请仲裁用户名</th>
                <th>仲裁用户联系方式</th>
                <th>仲裁专家</th>
                {{--<th>仲裁专家组员</th>--}}
                <th>申诉原因</th>
                <th>申诉时间</th>
                <th>申诉状态</th>
                <th>操作</th>
            </tr>
            </thead>
            <form >
            <tbody>
                @foreach($list as $ke=>$item)
                <tr>
                    <td class="center">
                        <label class="position-relative">
                            {{--<input type="checkbox" class="ace" />--}}
                            <span class="lbl">{{$ke+1}}</span>
                        </label>
                    </td>
                    <td><a target="view_window" href="{!! url('/task',$item->task_id) !!}">{{$item->user_task->title}}</a></td>
                    <td>
                        @if(isset($item->user_zc) && !empty($item->user_zc))
                        {{$item->user_zc->name}}
                        @endif
                    </td>
                    <td>
                        @if(isset($item->user_zc) && !empty($item->user_zc))
                        {{$item->user_zc->mobile}}</td>
                        @endif
                    <td>详情列表查看</td>
                    {{--<td>@foreach($item->ex_name_zh as $va) <a href="{!! url('/experts/detail',$va->id) !!}">{{$va->name}}</a> @endforeach</td>--}}
                    {{--<td>@foreach($item->ex_name_z as $val) <a href="{!! url('/experts/detail',$val->id) !!}">{{$val->name}}</a> @endforeach</td>--}}
                    <td>
                        @if(isset($item->user_zc) && !empty($item->user_zc))
                        {{$item->user_zc->reason}}
                        @endif
                    </td>
                    <td>
                        {{$item->user_task->updated_at}}
                    </td>
                    <td>
                        @if($item->user_task->status == 19) 仲裁中 @else 已线下解决(任务继续进行) @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-xs btn-info" href="/manage/arbitrationDetail/{{$item->id}}">
                                <i class="fa fa-edit"></i>详情
                            </a>
                            <a style="background-color: red!important;" class="btn btn-xs btn-info" href="/manage/arbitrationDel/{{$item->id}}">
                                <i class="fa fa-trash-o"></i>删除
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </form>
        </table>
        <div class="row">
            {{--<div class="col-md-2">
                <button class="btn btn-white btn-default btn-round">批量删除
                </button>
            </div>--}}
            {{--<a href="/manage/userAdd" target="_blank">添加</a>--}}
            <div class="col-md-12">
                <div class="dataTables_paginate paging_bootstrap text-right row">
                    <!-- 分页列表 -->
                    {{--{!! $list->appends($search)->render() !!}--}}
                    {!! $list->render() !!}
                </div>
            </div>
        </div>
        </div>
    </div>
    </div>

{!! Theme::asset()->container('custom-css')->usePath()->add('backstage', 'css/backstage/backstage.css') !!}

{{--时间插件--}}
{!! Theme::asset()->container('specific-css')->usePath()->add('datepicker-css', 'plugins/ace/css/datepicker.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('datepicker-js', 'plugins/ace/js/date-time/bootstrap-datepicker.min.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('userfinance-js', 'js/userfinance.js') !!}

{!! Theme::asset()->container('custom-js')->usePath()->add('checked-js', 'js/checkedAll.js') !!}
