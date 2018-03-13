
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">编辑自定义导航</h3>

    <div class="g-backrealdetails clearfix bor-border">
        <form action="/manage/editScope" method="post">
            {{ csrf_field() }}
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-md-1 text-right">类别：</p>
                <p class="col-md-11 text-left">
                    <select name="cate_id" id="cate_first">
                        @if(!empty($cate_first))
                            @foreach($cate_first as $item)
                                <option value="{!! $item['id'] !!}"
                                        @if(isset($scopeInfo[0]['cate_id']) && $scopeInfo[0]['cate_id'] == $item['id'])selected="selected" @endif>
                                    {!! $item['name'] !!}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </p>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="text-right col-xs-1"><lable>标题</lable></p>
                <p class="text-left col-xs-9">
                   <input type="text" name="name" id="name" value="{{$scopeInfo[0]['name']}}">
                    {{ $errors->first('name') }}
                    <input type="hidden" name="id" value="{{$scopeInfo[0]['id']}}">
                </p>
            </div>
            {{--<tr>
                <td class="text-right">样式：</td>
                <td class="text-left">
                    <input type="text" name="style" id="style" value="{{$navInfo[0]['style']}}">
                    {{ $errors->first('style') }}
                </td>
            </tr>--}}
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="text-right col-xs-1">排序</p>
                <p class="text-left col-xs-9">
                    <input type="text" name="sort" id="sort" value="{{$scopeInfo[0]['sort']}}">
                </p>
            </div>
            <div class="col-xs-12">
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
            {{--<tr>
                <td class="text-right col-xs-3"></td>
                <td class="text-left col-xs-9">
                    <button class="btn btn-primary btn-sm" type="submit">提交</button>
                </td>
            </tr>--}}
        </form>
    </div>


<!-- basic scripts -->
{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}