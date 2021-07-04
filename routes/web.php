<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/','IndexController@index')->name('index');

Route::get('/posts', 'IndexController@getPostsFromDates')->name('getPostsFromDates');
Route::get('/control', 'IndexController@control');
Route::get('/postsfromday', 'IndexController@getPostsFromDay')->name('getPostsFromDay');
Route::get('/postsSort', 'UploadController@postsSort')->name('postsSort');




Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
