<div class="col-xs-12 col-left">
        <div class="taskDetails alert taskbg clearfix">
            <div class="page-header">
                <h4 class="text-size22 cor-gray51"><strong>请上传合同附件及金额</strong></h4>
            </div>

            <div class="space"></div>
            <div class="tabbable">
                <div class="tab-content clearfix f-tab">
                    <!--余额支付-->
                    <div class="tab-pane in active clearfix text-size14 cor-gray51 u-pay" id="home1">
                            <form class="form-horizontal" role="form" action="/task/signContractUpdate" method="post"  name="bounty-form">
                                {{ csrf_field() }}
                                <div>
                                    <label style="color: #F00000">请使用平台提供的合同模板：</label>
                                    <label ><a target="_blank" href="/task/downFile/1">安全标准化</a> | </label>
                                    <label class=""><a target="_blank" href="/task/downFile/2">安全评价</a> | </label>
                                    <label class=""><a target="_blank" href="/task/downFile/3">技术咨询</a> | </label>
                                    <label class=""><a target="_blank" href="/task/downFile/4">委托检测</a> | </label>
                                    <label class=""><a target="_blank" href="/task/downFile/5">消防技术服务 | </a></label>
                                    <label class=""><a target="_blank" href="/task/downFile/6">职业病危害评价</a></label>
                                </div>
                                <div class="space"></div>
                                <input type="hidden" name="task_id" value="{{ $id }}" />
                                <input type="hidden" name="status" value="{{ $status }}" />
                                <label class="">请输入合同金额：</label>
                                <input type="number" placeholder=""  name="money"  class="inputxt" datatype="*6-15" errormsg="请输入合同金额">　　
                                <span style="color:#F00000">{!! $errors->first('money') !!}</span>
                                <div class="space"></div>
                                <label class="">请上传合同附件：</label>

                                <div class="annex">
                                    <!--文件上传-->
                                    <div  class="dropzone clea>fix" id="dropzone"
                                          url="{{ URL('task/fileUpload')}}"
                                          deleteurl="{{ URL('task/fileDelet') }}">
                                        <div class="fallback">
                                            <input name="file" type="file" multiple="" />
                                        </div>
                                    </div>
                                </div>
                                <div id="file_update"></div>
                                <div class="text-center clearfix">
                                    <button class="btn btn-primary btn-blue btn-big1 bor-radius2" >确认提交</button>
                                    <a href="/task/{{ $id }}" class="cor-gray93 btn-big">返回</a>
                                </div>
                            </form>

                    </div>

                </div>
            </div>
            {{--模态框--}}
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog"
                 aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header widget-header-flat">
                    <span class="modal-title text-size14">
                        支付提示
                    </span>
                        </div>
                        <div class="modal-body text-center clearfix">
                            <div class="col-sm-3 hidden-xs">
                                <div class="row text-right">
                                    <div class="space"></div>
                                    <span class="fa-stack cor-orange">
                                        <i class="fa fa-exclamation-circle
                                        fa-stack-2x"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-8 hidden-xs">
                                <div class="cor-gray51 text-left">
                                    <div class="space"></div>
                                    <h3 class="mg-margin text-size20 text-blod">请在打开的页面上完成付款！</h3>
                                    <h6 class="cor-gray97">付款完成前请不要关闭此窗口</h6>
                                    <div class="space"></div>
                                    <p>
                                        <a href="/task/{{ $id }}" type="button" class="btn btn-primary btn-sm btn-blue btn-big1 bor-radius2" >已完成付款</a>&nbsp;
                                        <a href="/user/unreleasedTasks" class="cor-blue167 text-under">支付遇到问题</a>
                                    </p>
                                    <p><a href="/task/bidBounty/{{ $id }}" class="cor-blue167 text-under">返回选择其他支付方式></a></p>
                                </div>
                            </div>
                            <div class="hidden-lg hidden-md hidden-sm visible-xs-12">
                                <div class="row text-center">
                                    <div class="space"></div>
                                    <span class="fa-stack cor-orange"><i class="fa fa-exclamation-circle fa-stack-2x"></i></span>
                                </div>
                            </div>
                            <div class="hidden-lg hidden-md hidden-sm visible-xs-12">
                                <div class="cor-gray51 text-center">
                                    <div class="space"></div>
                                    <h3 class="mg-margin text-size20 text-blod">请在打开的页面上完成付款！</h3>
                                    <h6 class="cor-gray97">付款完成前请不要关闭此窗口</h6>
                                    <div class="space"></div>
                                    <p>
                                        <a href="/task/{{ $id }}" type="button" class="btn btn-primary btn-sm btn-blue btn-big1 bor-radius2" >已完成付款</a>&nbsp;
                                        <a href="/user/unreleasedTasks" class="cor-gray97 modaltxt">支付遇到问题</a>
                                    </p>
                                    <p><a href="/task/bidBounty/{{ $id }}" class="cor-blue167" data-dismiss="modal">返回选择其他支付方式></a></p>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal -->
            </div>
        </div>
    </form>
</div>
{!! Theme::widget('popup')->render() !!}
{!! Theme::asset()->container('custom-css')->usepath()->add('issuetask','css/taskbar/issuetask.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('bounty-js','js/doc/bounty.js') !!}
{{--add by xl--}}
{{--{!! Theme::asset()->container('specific-css')->usepath()->add('issuetask','plugins/ace/css/dropzone.css') !!}--}}
{!! Theme::asset()->container('specific-css')->usepath()->add('chossen','css/ace/chosen.css') !!}
{{--{!! Theme::asset()->container('custom-js')->usepath()->add('ace-extra', 'plugins/ace/js/ace/ace-extra.min.js') !!}--}}
{!! Theme::widget('fileUpload')->render() !!}
{{--{!! Theme::asset()->container('custom-js')->usepath()->add('dropzone','plugins/ace/js/dropzone.min.js') !!}--}}
{!! Theme::asset()->container('custom-js')->usepath()->add('chosen', 'plugins/ace/js/chosen.jquery.min.js') !!}
