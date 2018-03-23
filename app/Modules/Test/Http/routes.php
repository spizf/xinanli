<?php




Route::group(['prefix' => 'test'], function() {
	/*Route::get('/', function() {
		dd('This is the Test module index page.');
	});*/
	Route::any('/testCallback','TestCallbackController@testCallback');
    Route::any('/evaluation','MarketingController@evaluation');//信用评价
    Route::any('/trusteeship','MarketingController@trusteeship');//资金托管
    Route::any('/arbitration','MarketingController@arbitration');//技术仲裁
});
