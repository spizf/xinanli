$(function(){
	$(".nav dl").hover(function(){
		
		$(this).toggleClass("on");
		$(this).children("dd").stop(true,true).slideToggle(200);
		$(".navbg").stop(true,true).slideToggle(200);
	});
	$(".nav .on").hover(function(){
		
		$(this).addClass("on");
		$(this).children("dd").stop(true,true).slideToggle(200);
		$(".navbg").stop(true,true).slideToggle(200);
	});
	$(".footertext dl a").hover(function(){
		$(this).children(".er").stop(true,true).slideToggle(200);
	});
	$(".leftsbo dl").hover(function(){
		$(this).toggleClass("on");
	});
//	$(".videobox").hover(function(){
//		$(this).children(".bofang").toggleClass("on");
//	});
	$(".fengcle").hover(function(){
		$(this).children("span").toggleClass("on");
	});
	$(".productsle").hover(function(){
		$(this).find(".protop").stop(true,true).slideToggle();
	})
})
