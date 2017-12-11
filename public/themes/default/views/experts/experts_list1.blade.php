
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta charset="utf-8" />
		<META http-equiv="X-UA-Compatible" content="IE=8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<!--<link rel="stylesheet" type="text/css" href="/themes/default/assets/experts/css/base.css" />-->
		<link rel="stylesheet" type="text/css" href="/themes/default/assets/experts/css/newcss.css" />
		<title>专家列表页</title>
	</head>
	<body>
		
		<div class="mainboxs">
				<div class="listtop">
				<dl>
					<dt>擅长服务</dt>
					<dd>
						<a class="@if(!isset($cate_id)) active @endif" href="{!! url('/experts/list/') !!}">全部</a>
						@foreach($cate as $v)
							<a
								@if(isset($cate_id)&&$cate_id==$v->id)
									class="active"
										{{--href="{!! url('/experts/list/addr', $v->id) !!}"--}}
								@endif
								@if(isset($addr_id)&&$addr_id)
									href="{!! url('/experts/list/'.$v->id.'/'.$cate_id) !!}"
								@else
									href="{!! url('/experts/list/'. $v->id.'/0') !!}"
								@endif
							>{!! $v->name !!}</a>
						@endforeach
					</dd>
					<span>
						<img src="/themes/default/assets/experts/img/xiaj.jpg">
					</span>
				</dl>
				<dl>
					<dt>地区分类</dt>
					<dd>
						<a class="@if(!isset($addr_id)) active @endif" href="{!! url('/experts/list/') !!}">全部</a>
						@foreach($district as $v)
							<a
								@if(isset($addr_id)&&$addr_id==$v->id)
							  		class="active"
							   {{--href="{!! url('/experts/list/addr', $v->id) !!}"--}}
							   @endif
							   @if(isset($cate_id)&&$cate_id)
							   		href="{!! url('/experts/list/'.$cate_id .'/'.$v->id) !!}"
							   @else
							   		href="{!! url('/experts/list/0/'. $v->id) !!}"
								@endif
							>{!! $v->name !!}</a>
						@endforeach
					</dd>
					<span>
						<img src="/themes/default/assets/experts/img/xiaj.jpg">
					</span>
				</dl>
			</div>
			<div class="listmo">
				<a href="{!! url('/experts/list') !!}" class="zong @if(!isset($sort)) active @endif">综合排序</a>
				<span></span>
				<a href="{!! url('/experts/list/ask_num') !!}" class="@if(isset($sort)&&$sort=='ask_num') active @endif">咨询量</a>
				<span></span>
				<a href="{!! url('/experts/list/satisfaction') !!}"  class="@if(isset($sort)&&$sort=='satisfaction') active @endif">满意度</a>
				<b>共有<i>{!! $count->total !!}</i>名专业顾问为您专业服务</b>
			</div>
			<div class="zongjian">
				@foreach($experts as $item)
				<div class="zongjianle" data="{!! $item->id !!}">
					<div class="touxiang zongjianle2">
						<img src="{!! url($item->head_img) !!}">
					</div>
					<h3>{!! $item->name !!}</h3>
					<h4>{!! $item->position !!}</h4>
					<div class="xinxi zongjianle2">
						<dl>
							<dd>{!! $item->year !!}年工作经验</dd>
							<dd>{!! $item->addr[0] !!}</dd>
							<dd>{!! $item->addr[1] !!}</dd>
						</dl>
					</div>
					<div class="shanchang zongjianle2">
						@foreach($item->cate as $v)
							<a href="">{!! $v !!}</a>
						@endforeach
					</div>
					<div class="dubox zongjianle2">
						<dl>
							<dt>{!! $item->recommend !!}</dt>
							<dd>推荐指数</dd>
						</dl>
						<span></span>
						<dl>
							<dt>{!! $item->satisfaction !!}%</dt>
							<dd>满意度</dd>
						</dl>
						<span></span>
						<dl>
							<dt>{!! $item->ask_num !!}</dt>
							<dd>咨询量</dd>
						</dl>
					</div>
					<div class="liji">
						@if($item->user&&0)
						<a href="javascript:;" title="联系TA" class="taskmessico" data-toggle="modal" data-target="#myModalgz" data-values="{!! $item->user->id !!}" data-id="{!! Theme::get('is_IM_open') !!}" >立即免费在线咨询</a>
						@endif
						{{--<a onclick="sentMessageToExperts('{!! $item->name !!}')"></a>--}}
					</div>
				</div>
				@endforeach
			</div>
            <?php echo $experts->render(); ?>
			@if(isset($ad)&&$ad)
				<div class="dibubanner">
					<a href="">
						<img src="/{{$ad->ad_file}}">
					</a>
				</div>
			@endif
		</div>
		<script type="text/javascript" src="/themes/default/assets/experts/js/jquery-1.8.3.min.js" ></script>
		<script>
			$(function(){
				$(".listtop dl span").click(function(){
					$(this).toggleClass("on");
					if($(this).hasClass("on")){
						$(this).prev("dd").css("height","auto");
						$(this).children("img").attr("src","/themes/default/assets/experts/img/shangj.jpg");
					}
					else{
						$(this).prev("dd").css("height","43px");
						$(this).children("img").attr("src","/themes/default/assets/experts/img/xiaj.jpg")
					}
				})
			});
			$('.zongjianle2').click(function(){
			    location.href="{!! url('experts/detail') !!}"+'/'+$(this).parent().attr('data');
			});
		</script>
	</body>

</html>