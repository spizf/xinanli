
/*$('.add_work').click(function () {
    $('.add_work').before("<input type=\"text\" placeholder=\"\"  name=\"workexpert[]\"  class=\"inputxt work_\" datatype=\"zh2-4\" errormsg=\"请输入2到4位中文字符\" nullmsg=\"请输入作业专家！\" > <input type=\"button\" onclick=\"del_zuoyezhuanjia(this)\" name=\"del-text\" value=\"删除\">");
});

$('.add_review').click(function () {
    $('.add_review').before("<input type=\"text\" placeholder=\"\"  name=\"reviewexpert[]\"  class=\"inputxt review_\" datatype=\"zh2-4\" errormsg=\"请输入2到4位中文字符\" nullmsg=\"请输入评审专家\"> ");
});*/

$(function(){
    var i = 2;
    $('.add_work').click(function(){
        if(i < 100) {
            $('#Inputwork').append("<div style=\"margin-bottom: 10px;\"><input type=\"text\" placeholder=\"\"  name=\"workexpert[]\"  class=\"inputxt work_\" datatype=\"zh2-4\" errormsg=\"请输入2到4位中文字符\" nullmsg=\"请输入作业专家！\" > <span onclick=\"del_work(this)\" class=\"label label-primary del_work\">x</span> <span class=\"Validform_checktip\"></span></div>");
            i++;
        } else {
            alert("最多加100个");
        }

    });
    $('.add_review').click(function(){
        if(i < 100) {
            $('#Inputreview').append("<div style=\"margin-bottom: 10px;\"><input type=\"text\" placeholder=\"\"  name=\"reviewexpert[]\"  class=\"inputxt review_\" datatype=\"zh2-4\" errormsg=\"请输入2到4位中文字符\" nullmsg=\"请输入评审专家！\" > <span onclick=\"del_review(this)\" class=\"label label-primary del_review\">x</span> <span class=\"Validform_checktip\"></span></div>");
            i++;
        } else {
            alert("最多加100个");
        }

    });
});

function del_work(e){
    $(e).parent().remove();
    i--;
}
function del_review(e){
    $(e).parent().remove();
    i--;
}