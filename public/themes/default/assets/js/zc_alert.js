
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
                location.reload();
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
//通知提交仲裁资料
$(".messubm").click(function () {
    var mesgs = $("#mesg").serializeArray();
    var token = $('input[name="_token"]').val();
    $.ajax({
        type:'post',
        url: '/task/sendAtributMessage',
        data:{mesgs:mesgs, _token:token},
        dataType:"json",
        success:function(data) {
            if (data.status =='10002') {
                $('#mes').val('');
                location.reload();
                $.gritter.add({
                    text: '<div><span class="text-center"><h5>data.msg</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }else if(data.status =='10001'){
                $.gritter.add({
                    text: '<div><span class="text-center"><h5>data.msg</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }else{
                $.gritter.add({
                    text: '<div><span class="text-center"><h5>data.msg</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }
        }
    })
});

