
<h3 class="header smaller lighter blue mg-bottom20 mg-top12">添加业务范围</h3>

<div class="g-backrealdetails clearfix bor-border">
        <form action="/manage/addScope" method="post">
            {{ csrf_field() }}
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">类别：</p>
                <p class="col-md-11 text-left">
                    <select name="cate_id" id="cate_first">
                        @if(!empty($cate_first))
                            @foreach($cate_first as $item)
                                <option value="{!! $item['id'] !!}"
                                        @if(isset($success_case->cate_pid) && $success_case->cate_pid == $item['id'])selected="selected" @endif>
                                    {!! $item['name'] !!}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </p>
            </div>

            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">标题：</p>
                <p class="col-md-11 text-left">
                   <input type="text" name="name" id="name" value="">
                    <span class="red">{{ $errors->first('name') }}</span>
                </p>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">排序：</p>
                <p class="col-md-11 text-left">
                    <input type="text" name="sort" id="sort" value="">
                </p>
            </div>
            <div class="col-md-12">
                <div class="clearfix row bg-backf5 padding20 mg-margin12">
                    <div class="col-xs-12">
                        <div class="col-md-1 text-right"></div>
                        <div class="col-md-10">

                            <button class="btn btn-primary btn-sm" type="submit">提交</button>
                            <a href="javascript:history.back()" title="" class=" add-case-concel">返回</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}
<!-- basic scripts -->
