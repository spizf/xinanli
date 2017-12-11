<?php




//Route::group(['prefix' => 'experts','middleware' => 'auth'], function() {
Route::group(['prefix' => 'experts'], function() {

	Route::get('/list','ExpertsController@expertsList')->name('expertsList');
    Route::get('/list/{sort}','ExpertsController@expertsList')->name('expertsList');
    Route::get('/list/{cate_id}/{addr_id}','ExpertsController@expertsList')->name('expertsList');
    Route::get('/sentMessageToExperts/{name}','ExpertsController@sentMessageToExperts')->name('expertsList');

	Route::get('/detail','ExpertsController@expertsDetail')->name('expertsDetail');
	Route::get('/detail/{id}','ExpertsController@expertsDetail')->name('expertsDetail');

});
