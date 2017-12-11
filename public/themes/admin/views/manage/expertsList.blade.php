   {{-- <div class="page-header">
        <h3>
              搜索
        </h3>
    </div><!-- /.page-header -->--}}
    <h3 class="header smaller lighter blue mg-top12 mg-bottom20">仲裁专家</h3>
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix  well">
            <div class="">
                <div style="float:left">
                <form  role="form" class="form-inline search-group" action="{!! url('manage/experts') !!}" method="get">
                    <div class="form-group search-list">
                        <label for="">姓名　　</label>
                        <input type="text" name="username" @if(isset($username)) value="{!! $username !!}" @endif/>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-sm">搜索</button>
                    </div>
                 </form>
                </div>
                <div class="form-group" style="float:right">
                    <a href="{!! url('manage/expertsAdd') !!}"><button class="btn btn-primary btn-sm">添加仲裁专家</button></a>
                </div>
            </div>
            <div class="space"></div>
            <div style="clear:both;">
                <div class="form-group search-list width285 addrAll" style="width:100%;#display: none">
                    <label>专家地区　　</label>
                    <select class="addr1 addr" name="addr[]"  id="addr1">
                        <option value="-1">请选择省份</option>
                        @foreach($district as $v)
                            <option value="{{$v->id}}"
                                @if(session('expertsAddr')['addr1']==$v->id)
                                    selected
                                @endif
                            >{{$v->name}}</option>
                        @endforeach
                    </select>
                    <select class="addr2 addr" name="addr[]"  id="addr2">
                        <option value="-1"> 请选择城市</option>
                    </select>
                    <select class="addr3 addr" name="addr[]" id="addr3">
                        <option value="-1">请选择地区</option>
                    </select>
                </div>
                <script>
                    function getAddr2(val){
                        $.ajax({
                            type:'GET',
                            url:"/getDistrict/"+val,
                            success:function(data){
                                var str='<option  value="-1">请选择城市</option>';
                                for(var i=0;i<data.length;i++) {
                                    @if(session('expertsAddr')['addr2'])
                                        if({{session('expertsAddr')['addr2']}}==data[i].id){
                                            str += "<option value='" + data[i].id + "' selected>"+data[i].name+"</option>";
                                        }else {
                                            str += "<option value='" + data[i].id + "'>" + data[i].name + "</option>";
                                        }
                                    @else
                                        str += "<option value='" + data[i].id + "'>" + data[i].name + "</option>";
                                    @endif
                                }
                                $('#addr2').html(str);
                            }
                        });
                    }
                    @if(session('expertsAddr')['addr1'])
                        getAddr2({{session('expertsAddr')['addr1']}});
                    @endif
                    function getAddr3(val){
                        $.ajax({
                            type:'GET',
                            url:"/getDistrict/"+val,
                            success:function(data){
                                var str='<option value="-1">请选择地区</option>';
                                for(var i=0;i<data.length;i++) {
                                    @if(session('expertsAddr')['addr3'])
                                    if({{session('expertsAddr')['addr3']}}==data[i].id){
                                        str += "<option value='" + data[i].id + "' selected>"+data[i].name+"</option>";
                                    }else {
                                        str += "<option value='" + data[i].id + "'>" + data[i].name + "</option>";
                                    }
                                    @else
                                        str += "<option value='" + data[i].id + "'>" + data[i].name + "</option>";
                                    @endif
                                }
                                $('#addr3').html(str);
//                                $('.addrAll').show()
                            }
                        });
                    }
                    @if(session('expertsAddr')['addr2'])
                        getAddr3({{session('expertsAddr')['addr2']}});
                    @endif
                    $('.addr').change(function(){
                        $.ajax({
                            type:'POST',
                            url:"/manage/showExpertsAddr",
                            data:{'addr1':$('.addr1').val(),'addr2':$('.addr2').val(),'addr3':$('.addr3').val(),'_token':'{{csrf_token()}}'},
                            dataType:'json',
                            success:function(data){
                                if(data){
                                    location.reload();
                                }
                            }
                        });
                    });
                </script>
            </div>
    </div>

    <!-- <div class="table-responsive"> -->

    <!-- <div class="dataTables_borderWrap"> -->
    <div>
        <table id="sample-table" class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th class="center">
                    <label class="position-relative">
                        <input type="checkbox" class="ace" />
                        <span class="lbl"></span>
                        UID
                    </label>
                </th>
                <th>姓名</th>
                <th>职称</th>
                <th>职级</th>
                <th>所属地区</th>
                <th>入驻时间</th>
                <th>从业年限</th>
                <th>专家等级</th>
                <th>操作</th>
            </tr>
            </thead>
            <form >
            <tbody>
                @foreach($list as $item)
                <tr>
                    <td class="center">
                        <label class="position-relative">
                            <input type="checkbox" class="ace" />
                            <span class="lbl"></span>{!! $item->id !!}
                        </label>
                    </td>
                    <td>
                        <a href="#">{!! $item->name !!}</a>
                    </td>
                    <td>{!! $item->position !!}</td>
                    <td>{!! $item->position_level !!}</td>
                    <td>
                        {!! $item->addr !!}
                    </td>
                    <td>
                       {!! $item->add_time !!}
                    </td>
                    <td>
                        {!! $item->year !!}
                    </td>
                    <td>
                        {!! $item->level !!}
                    </td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-xs btn-info" href="{!! url('manage/expertsEdit/' . $item->id) !!}">
                                <i class="fa fa-edit"></i>编辑
                            </a>
                            <a style="background-color:green!important" class="btn btn-xs btn-info" href="{!! url('manage/expertsWork/' . $item->id) !!}">
                                <i class="fa fa-edit"></i>履历
                            </a>
                            <a style="background-color:red!important" class="btn btn-xs btn-info" href="{!! url('manage/expertsDel/' . $item->id) !!}">
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
