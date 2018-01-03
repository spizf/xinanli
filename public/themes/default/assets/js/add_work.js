
;$('.add_work').click(function () {
    $('.add_work').before("<input type=\"text\" placeholder=\"\"  name=\"workexpert[]\"  class=\"inputxt work_\" datatype=\"zh2-4\" errormsg=\"请输入2到4位中文字符\" nullmsg=\"请输入作业专家！\" > ");
});

$('.add_review').click(function () {
    $('.add_review').before("<input type=\"text\" placeholder=\"\"  name=\"reviewexpert[]\"  class=\"inputxt review_\" datatype=\"zh2-4\" errormsg=\"请输入2到4位中文字符\" nullmsg=\"请输入评审专家\"> ");
});