<?php




Route::group(['prefix' => 'task','middleware' => 'auth'], function() {

	
	Route::get('/create','IndexController@create')->name('taskCreatePage');
	Route::post('/createTask','IndexController@createTask')->name('taskCreate');
	Route::post('/fileUpload','IndexController@fileUpload')->name('fileCreate');
	Route::get('/fileDelet','IndexController@fileDelet')->name('fileDelete');
	Route::get('/bounty/{id}','IndexController@bounty')->name('bountyPage');
	Route::get('/getTemplate','IndexController@getTemplate')->name('ajaxTemplate');
	Route::get('/preview','IndexController@preview')->name('previewPage');
	Route::get('/release/{id}','IndexController@release')->name('releaseDetail');
	Route::get('/tasksuccess/{id}','IndexController@tasksuccess')->name('tasksuccess');
	
	Route::post('/workCreate','DetailController@workCreate')->name('workCreate');
	Route::get('/workdelivery/{id}','DetailController@work')->name('workdeliveryPage');

	Route::post('/ajaxAttatchment','DetailController@ajaxWorkAttatchment')->name('ajaxCreateAttatchment');
	Route::get('/delAttatchment','DetailController@delAttatchment')->name('attatchmentDelete');
	Route::get('/winBid/{work_id}/{task_id}','DetailController@winBid')->name('winBid');
	Route::get('/download/{id}','DetailController@download')->name('download');
	Route::get('/delivery/{id}','DetailController@delivery')->name('taskdeliveryPage');
	Route::post('/deliverCreate','DetailController@deliverCreate')->name('deliverCreate');
	Route::get('/check','DetailController@workCheck')->name('check');
	Route::get('/lostCheck','DetailController@lostCheck')->name('lostCheck');
	Route::get('/evaluate','DetailController@evaluate')->name('evaluatePage');
	Route::post('/evaluateCreate','DetailController@evaluateCreate')->name('evaluateCreate');
	
	Route::post('/ajaxRights','DetailController@ajaxRights')->name('ajaxCreateRights');
	
	Route::post('/report','DetailController@report')->name('reportCreate');

	
	Route::get('/getComment/{id}','DetailController@getComment')->name('commentList');
	Route::post('/ajaxComment','DetailController@ajaxComment')->name('ajaxCreateComment');

	
	Route::post('/bountyUpdate','IndexController@bountyUpdate')->name('bountyUpdate');
	Route::get('/result','IndexController@result')->name('resultCreate');
	Route::post('/notify','IndexController@notify')->name('notifyCreate');
	
	Route::get('/weixinNotify','IndexController@weixinNotify')->name('weixinNotifyCreate');

	
	Route::get('/ajaxcity','IndexController@ajaxcity')->name('ajaxcity');
	Route::get('/ajaxarea','IndexController@ajaxarea')->name('ajaxarea');

	
	Route::get('/imgupload','IndexController@imgupload')->name('imgupload');

	
	
	Route::post('/checkDeadlineByBid','IndexController@checkDeadlineByBid')->name('checkDeadlineByBid');
	
	Route::get('/buyServiceTaskBid/{id}','IndexController@buyServiceTaskBid')->name('buyServiceTaskBid');
	
	Route::post('/buyServiceTaskBid','IndexController@postBuyServiceTaskBid')->name('postBuyServiceTaskBid');

	
	Route::get('/tenderWork/{id}','DetailController@tenderWork')->name('tenderWork');
	
	Route::get('/bidWinBid/{work_id}/{task_id}','DetailController@bidWinBid')->name('bidWinBid');
	
	Route::get('/bidBounty/{id}','IndexController@bidBounty')->name('bidBounty');
    //仲裁费
    Route::get('/arbitrationBounty/{id}','IndexController@arbitrationBounty')->name('arbitrationBounty');


    Route::post('/bidBountyUpdate','IndexController@bidBountyUpdate')->name('bidBountyUpdate');
    //支付仲裁费
    Route::post('/arbitrationBountyUpdate','IndexController@arbitrationBountyUpdate')->name('arbitrationBountyUpdate');
    //提交仲裁附件
	Route::post('/bidBountyUpdate','IndexController@bidBountyUpdate')->name('bidBountyUpdate');

	Route::get('/payType/{id}','DetailController@payType')->name('payType');
	
	Route::get('/ajaxPaySection','DetailController@ajaxPaySection')->name('ajaxPaySection');
	
	Route::post('/postPayType','DetailController@postPayType')->name('postPayType');
	
	Route::get('/checkPayType/{taskid}/{status}','DetailController@checkPayType')->name('checkPayType');

	Route::get('/payTypeAgain/{id}','DetailController@payTypeAgain')->name('payTypeAgain');
	
	Route::get('/bidDelivery/{id}','DetailController@bidDelivery')->name('bidDelivery');
	Route::post('/bidDeliverCreate','DetailController@bidDeliverCreate')->name('bidDeliverCreate');
	
	Route::get('/bidWorkCheck','DetailController@bidWorkCheck')->name('bidWorkCheck');
	
	Route::post('/ajaxBidRights','DetailController@ajaxBidRights')->name('ajaxBidRights');
	//专家提交仲裁报告
	Route::post('/submitAccessory','DetailController@submitAccessory')->name('submitAccessory');
	//仲裁附件提交
	Route::post('/reasonAccessory','DetailController@reasonAccessory')->name('reasonAccessory');
	//记录筛选专家状态
	Route::post('/expertFirst','DetailController@expertFirst')->name('expertFirst');
    //申请仲裁
    Route::post('/submitExperts','IndexController@submitExperts')->name('submitExperts');
    //add by xl
    Route::get('/changeStatus/{id}/{status}','DetailController@changeStatus')->name('changeStatus');
    Route::get('/changeWibBid/{id}/{status}','DetailController@changeWibBid')->name('changeWibBid');
    Route::get('/signContract/{id}/{status}','IndexController@signContract')->name('signContract');
    Route::post('/signContractUpdate','IndexController@signContractUpdate')->name('signContractUpdate');//报告交付
    Route::get('/offlinePayment/{id}','IndexController@offlinePayment')->name('offlinePayment');//线下支付
    Route::get('/getField/','IndexController@getField')->name('getField');//获取行业列表
    Route::get('/downFile/{id}','IndexController@downFile')->name('downFile');//下载合同模板
});


Route::group(['prefix'=>'task'],function(){
	
	Route::get('/','IndexController@tasks')->name('taskList');

    Route::get('/{id}','DetailController@index')->name('taskDetailPage')->where('id', '[0-9]+');
    Route::post('/reasonTask','DetailController@reasonTask');
    //通知双方上传仲裁资料
    Route::post('/sendAtributMessage','DetailController@sendatributMessage');
	Route::get('/successCase','SuccessCaseController@index')->name('successCaseList');
	Route::get('/successDetail/{id}','SuccessCaseController@detail')->name('successDetail');
	Route::get('/successJump/{id}','SuccessCaseController@jump')->name('successJump');
	
	Route::post('/checkbounty','IndexController@checkBounty')->name('checkbounty');
	Route::post('/checkdeadline','IndexController@checkDeadline')->name('checkdeadline');

	
	Route::get('/ajaxPageWorks/{id}','DetailController@ajaxPageWorks')->name('ajaxPageWorks');
	Route::get('/ajaxPageDelivery/{id}','DetailController@ajaxPageDelivery')->name('ajaxPageDelivery');
	Route::get('/ajaxPageComment/{id}','DetailController@ajaxPageComment')->name('ajaxPageComment');

	
	Route::get('/collectionTask/{task_id}','IndexController@collectionTask');
	Route::post('/collectionTask','IndexController@postCollectionTask');
	
	Route::get('/rememberTable','DetailController@rememberTable');


});
