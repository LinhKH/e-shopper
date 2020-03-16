<?php

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/admin', 'AdminController@login');
Route::match(['get','post'], '/admin', 'AdminController@login');
// Route::get('/admin/dashboard', 'AdminController@dashboard');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['auth']], function() {
    Route::get('/admin/dashboard', 'AdminController@dashboard');
    Route::get('/admin/settings', 'AdminController@settings');
    Route::get('/admin/check-pwd', 'AdminController@checkpwd');
    Route::match(['get', 'post'],'/admin/update-pwd','AdminController@updatePassword');

    //Categories
    Route::get('/admin/view-categories', 'CategoryController@viewCategories');
    Route::match(['get', 'post'], '/admin/add-category','CategoryController@addCategory');
    Route::match(['get', 'post'], '/admin/edit-category/{id}','CategoryController@editCategory');
    Route::match(['get', 'post'], '/admin/delete-category/{id}','CategoryController@deleteCategory');
});

Route::get('/logout','AdminController@logout');
