<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

	<head>
		<meta charset="utf-8" />
		<META http-equiv="X-UA-Compatible" content="IE=8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" type="text/css" href="/themes/default/assets/experts/css/newcss.css" />
		<title>专家详情页</title>
	</head>

	<body>
		<div class="mainboxs">
			<div class="xiangqingtop">
				<div class="xiangqinle">
					<div class="xiangqinglet">
						<div class="xiangqingletx">
							<img src="{!! url($data->head_img) !!}">
						</div>
						<div class="xiangqingletr">
							<div class="xiangqintt">
								<h2>{!! $data->name !!}</h2>
								<span>{!! $data->position !!}</span>
							</div>
							<div class="xiangqintm">
								{!! $data->year !!}年工作经验 丨 {!! $data->addr[0] !!} {!! $data->addr[1] !!}
							</div>
							<div class="shanchang shanchang2">
								@foreach($data->cate as $v)
									<a href="#">{!! $v !!}</a>
								@endforeach
							</div>
						</div>
					</div>
					<div class="xiangqingleb">
						<h1><b>关于我</b><span></span></h1>
						<div class="aboutb">
							{!! $data->detail !!}
						</div>
						<div class="zhankai">
							<a href="javascript:;">展开</a>
						</div>
					</div>
				</div>
				<div class="xiangqinri">
					<div class="lijim">

						@if($data->user)
							<a href="javascript:;" title="联系TA" class="taskmessico" data-toggle="modal" data-target="#myModalgz" data-values="{!! $data->user->id !!}" data-id="{!! Theme::get('is_IM_open') !!}" >立即免费在线咨询</a>
						@endif
					</div>
					<div class="tuijzhishu">
						<dl>
							<dt>推荐指数：</dt>
							<dd>
								@for ($i = 0; $i < $data->recommend; $i++)
									<img src="/themes/default/assets/experts/img/xingxing.jpg">
									{{--<img src="img/xingxing.jpg">--}}
								@endfor
							</dd>
							<span>{!! $data->recommend !!}</span>
						</dl>
					</div>
					<div class="tuijqi">
						<dl>
							<dt>平均响应时间(分钟)</dt>
							<dd>{!! $data->do_time !!}</dd>
						</dl>
						<dl>
							<dt>咨询满意度</dt>
							<dd>{!! $data->satisfaction !!}%</dd>
						</dl>
						<dl>
							<dt>累计帮助用户</dt>
							<dd>{!! $data->service_num !!}</dd>
						</dl>
					</div>
				</div>
			</div>
			<div class="fuwulvl">
				<div class="fuwulvlle">
					<div class="fuwutop">
						<span>服务履历</span>
					</div>
					<div class="fuwubo">
						@if(isset($work))
						<div class="fuwubole">
							<img src="{!! url($work->img) !!}">
						</div>
						<div class="fuwubori">
							<h2>{!! $work->company !!}</h2>
							<dl>
								<dt>工作时间：</dt>
								<dd>{!! $work->time !!}</dd>
							</dl>
							<dl>
								<dt>在职职位：</dt>
								<dd>{!! $work->position !!}</dd>
							</dl>
							<dl class="zs">
								<dt>工作职责：</dt>
								<dd>{!! $work->work !!}
								</dd>
								<a class="zhizez" href="javascript:;">展开</a>
							</dl>
						</div>
						@endif
					</div>
				</div>
				<style>
					.fuwulvlri{
						display:none;
					}
				</style>
				<script>
					function changeTuijian(obj,id){
					    var data='<?php echo json_encode($id);?>';
                        var result=(typeof data == 'string') ? JSON.parse(data) : data;
                        for(var i=0; i<result.length; i++) {
                            if(result[i] == id) {
                                result.splice(i, 1);
                                break;
                            }
                        }
                        var index = Math.floor((Math.random()*result.length));
                        if($(obj).parent().parent().attr('data')==id){
                            $('.fuwulvlri').each(function(){
//                                if($(this).attr('data')==result[index]){
                                if($(this).attr('data')==result[index]&&result[index]!=id){
                                    $(obj).parent().parent().hide();
                                    $(this).show();
								}
							});
						}
					}
				</script>
				@foreach($experts as $k=>$v)
				<div class="fuwulvlri" data="{!! $v->id !!}" @if($k==0)style="display:block"@endif>
					<div class="fuwutop">
						<span>推荐</span>
						<a onclick="changeTuijian(this,'{!! $v->id !!}')">不合适，换一组</a>
					</div>
					<div class="zongjianle"  data="{!! $v->id !!}">
						<div class="touxiang zongjianle2">
							<img src="{!! url($v->head_img) !!}">
						</div>
						<h3>{!! $v->name !!}</h3>
						<h4>{!! $v->position !!}</h4>
						<div class="xinxi zongjianle2">
							<dl>
								<dd>{!! $v->year !!}年工作经验</dd>
								<dd>{!! $v->addr[0] !!}</dd>
								<dd>{!! $v->addr[1] !!}</dd>
							</dl>
						</div>
						<div class="shanchang zongjianle2">
							@foreach($v->cate as $vv)
								<a href="">{!! $vv !!}</a>
							@endforeach
						</div>
						<div class="dubox zongjianle2">
							<dl>
								<dt>{!! $v->recommend !!}</dt>
								<dd>推荐指数</dd>
							</dl>
							<span></span>
							<dl>
								<dt>{!! $v->satisfaction !!}%</dt>
								<dd>满意度</dd>
							</dl>
							<span></span>
							<dl>
								<dt>{!! $v->ask_num !!}</dt>
								<dd>咨询量</dd>
							</dl>
						</div>
						<div class="liji">
							@if($v->user)
								<a href="javascript:;" title="联系TA" class="taskmessico" data-toggle="modal" data-target="#myModalgz" data-values="{!! $v->user->id !!}" data-id="{!! Theme::get('is_IM_open') !!}" >立即免费在线咨询</a>
							@endif
						</div>
					</div>
				</div>
				@endforeach
			</div>
			@if(isset($ad)&&$ad)
				<div class="dibubanner" style="margin-top:64px;">
					<a href="">
						<img src="/{{$ad->ad_file}}">
					</a>
				</div>
			@endif
		</div>
		<script type="text/javascript" src="/themes/default/assets/experts/js/jquery-1.8.3.min.js"></script>
		<script>
			$(function() {
				var heights = $(".aboutb").height();
				var heightss = $(".zs dd").height();
				console.log(heights);
				if(heights < 98){
					$(".zhankai").hide();
				};
				if(heightss < 143){
					$(".zhizez").hide();
				};
				$(".zhankai a").click(function() {
					$(this).toggleClass("on");
					if($(this).hasClass("on")) {
						$(this).parent().prev(".aboutb").css("max-height", "auto");
						$(this).text("收起");
					} else {
						$(this).parent().prev(".aboutb").css("max-height", "143px");
						$(this).text("展开");
					}

				});
				$(".zs a").click(function() {
					$(this).toggleClass("on");
					if($(this).hasClass("on")) {
						$(this).prev("dd").css("max-height", "auto");
						$(this).text("收起");
					} else {
						$(this).prev("dd").css("max-height", "98px");
						$(this).text("展开");
					}

				})
			})
            $('.zongjianle2').click(function(){
                location.href="{!! url('experts/detail') !!}"+'/'+$(this).parent().attr('data');
            });
		</script>
	</body>

</html>