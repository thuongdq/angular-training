<?php
session_start();
require('cores.php');
define('BASEURL', 'your_url');

// Khởi chạy sự kiện
// Event::disPatcher();

App::start();

App::filter(['isLogin', 'login', 'logout', 'register'], function() {
	header('Content-Type: application/json');
	return true;
});

App::filter(['users.*', 'categories.*', 'posts.*'], function() {
	header('Content-Type: application/json');
	// if (!User::isLogin()) {				
	// 	Response::warning('Bạn không được phép truy cập');
	// 	Response::printData();
	// 	return false;
	// }
	return true;
});

// Auth Controller
Route::get('isLogin', 'AuthController@isLogin');
Route::post('login', 'AuthController@login');
Route::get('logout', 'AuthController@logout');
Route::post('register', 'AuthController@register');

// User Controller
Route::get('users', 'UserController@index');
Route::post('users/create', 'UserController@create');
Route::get('users/{$id}/show', 'UserController@show');
Route::put('users/{$id}/update', 'UserController@update');
Route::delete('users/{$id}/delete', 'UserController@delete');

// Category Controller
Route::get('categories', 'CategoryController@index');
Route::post('categories/create', 'CategoryController@create');
Route::get('categories/{$id}/show', 'CategoryController@show');
Route::put('categories/{$id}/update', 'CategoryController@update');
Route::delete('categories/{$id}/delete', 'CategoryController@delete');

// Tag Controller
Route::get('tags', 'TagController@index');
Route::post('tags/create', 'TagController@create');
Route::get('tags/{$id}/show', 'TagController@show');
Route::put('tags/{$id}/update', 'TagController@update');
Route::delete('tags/{$id}/delete', 'TagController@delete');

// Post Controller
Route::get('posts', 'PostController@index');
Route::post('posts/create', 'PostController@create');
Route::get('posts/creator', 'PostController@creator');
Route::get('posts/{$id}/show', 'PostController@show');
Route::put('posts/{$id}/update', 'PostController@update');
Route::delete('posts/{$id}/delete', 'PostController@delete');

// Dashboard Controller
Route::get('dashboard', 'DashboardController@index');
Route::get('dashboard/{$id}/category', 'DashboardController@getPostsByCategory');
Route::get('dashboard/{$id}/detail', 'DashboardController@detail');
Route::post('dashboard/{$id}/detail', 'DashboardController@comment');
Route::get('dashboard/{$id}/detail/comments', 'DashboardController@getComments');
Route::get('dashboard/votes', 'DashboardController@votes');

App::end();