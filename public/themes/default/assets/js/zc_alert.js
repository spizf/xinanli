
//提交仲裁原因
$(".subm").click(function () {
    var reasons = $("#reason").serializeArray();
    var token=$('input[name="_token"]').val();
    $.ajax({
        type:'post',
        url: '/task/reasonTask',
        data:{reasons:reasons, _token:token},
        dataType:"json",
        success:function (data) {
            if (data.status =='1') {
                $('#res').val('');
                $.gritter.add({
                    text: '<div><span class="text-center"><h5>提交成功,支付仲裁费后开启仲裁!</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }else{
                $.gritter.add({
                    text: '<div><span class="text-center"><h5>提交失败!</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }
        }
    })
});

