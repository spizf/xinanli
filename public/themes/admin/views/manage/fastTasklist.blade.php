
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">快速发布需求</h3>
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix  well">
            <div class="form-inline search-group">
                <form  role="form" action="/manage/fastTask" method="get">
                    <div class="form-group search-list">
                        <label for="name">需求标题　</label>
                        <input type="text" class="form-control" id="task_title" name="task_title" placeholder="请输入需求标题" @if(isset($merge['task_title']))value="{!! $merge['task_title'] !!}"@endif>
                    </div>
                    <div class="form-group search-list">
                        <label for="namee">用户名　　</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名" @if(isset($merge['username']))value="{!! $merge['username'] !!}"@endif>
                    </div>
                    <div class="form-group">
                    	<button type="submit" class="btn btn-primary btn-sm">搜索</button>
                    </div>

                </form>
            </div>
        </div>
        <div>
            <table id="sample-table" class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th>编号</th>
                    <th>用户名</th>
                    <th>手机号</th>
                    <th>需求标题</th>
                    <th>生产产品</th>
                    <th>
                        年规模/产量
                    </th>
                    <th>
                        需求类型
                    </th>
                    <th>
                        行业
                    </th>
                    <th>
                        地区
                    </th>
                    <th>
                        发布时间
                    </th>
                    <th>
                        状态
                    </th>
                    <th>
                        操作
                    </th>
                </tr>
                </thead>
                    <tbody>
                    @foreach($task as $item)
                        <tr>
                            <td>
                                <a href="#">{!! $item->id !!}</a>
                            </td>
                            <td>{!! $item->user !!}</td>
                            <td>{!! $item->mobile !!}</td>
                            <td>{!! $item->taskName !!}</td>
                            <td>{!! $item->productName !!}</td>
                            <td>{!! $item->productNum !!}</td>
                            <td>{!! $item->cate !!}</td>
                            <td>{!! $item->industry !!}</td>
                            <td>{!! $item->addr !!}</td>
                            <td>{!! $item->create_time !!}</td>
                            <td>
                                @if($item->status==1)
                                    <span style="color:green">已处理</span>
                                @elseif($item->status==2)
                                    <span style="color:red">已放弃</span>
                                @else
                                    <span style="color:orangered">未处理</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status==0)
                                <div class="hidden-sm hidden-xs btn-group">
                                        <a class="btn btn-xs btn-success" onclick="changeTaskStatus('{{$item->id}}',1)">
                                            <i class="ace-icon fa fa-check bigger-120">已处理</i>
                                        </a>
                                        <a class="btn btn-xs btn-danger" onclick="changeTaskStatus('{{$item->id}}',2)">
                                            <i class="ace-icon fa fa-minus-circle bigger-120"> 放弃</i>
                                        </a>
                                </div>
                                @else
                                    操作人：{{$item->realname}}
                                @endif
                            </td>
                            <script>
                                function changeTaskStatus(id,status){
                                    $.ajax({
                                        url:'/manage/changeTaskStatus/'+id+'/'+status,
                                        type:'get',
                                        success:function(){
                                            location.reload();
                                        }
                                    });
                                }
                            </script>
                        </tr>
                    @endforeach
                    </tbody>
                </form>
            </table>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="dataTables_paginate paging_simple_numbers row" id="dynamic-table_paginate">
                    {!! $task->appends($merge)->render() !!}
                </div>
            </div>
        </div>
    </div>
</div><!-- /.row -->


{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}

{{--时间插件--}}
{!! Theme::asset()->container('specific-css')->usePath()->add('datepicker-css', 'plugins/ace/css/datepicker.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('datepicker-js', 'plugins/ace/js/date-time/bootstrap-datepicker.min.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('userfinance-js', 'js/userfinance.js') !!}