<?php




Route::get('/', 'HomeController@index');

Route::get('/getDistrict/{id}', 'HomeController@getDistrict')->name('indexList');
Route::get('/getField/{id}', 'HomeController@getField')->name('indexList');
Route::post('/fastTask', 'HomeController@fastAddTask')->name('indexList');
//Route::post('/checkMobileCode', 'HomeController@checkMobileCode')->name('indexList');