var index = {

	picsEnlarge:function(obj,className){
		obj.hover(function(){
			$(this).addClass(className);
		},function(){
			$(this).removeClass(className)
		})
	},

}

function dropdownOpen() {

    var $dropdownLi = $('li.dropdown');

    $dropdownLi.mouseover(function() {
        $(this).addClass('open');
    }).mouseout(function() {
        $(this).removeClass('open');
    });
}


$(function(){

	index.picsEnlarge($('.item-holder'),'active');

    $(document).off('click.bs.dropdown.data-api');

    dropdownOpen();//调用


})

