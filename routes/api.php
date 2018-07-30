<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/me', function (Request $request) {
    return $request->user();
});

Route::post('oauth/login')
	->uses('Api\AuthController@login');

Route::post('oauth/register')
	->uses('Api\AuthController@register');

Route::get('images')
	->uses('Api\ImageController@getAllUploads');

Route::get('images/recent')
	->uses('Api\ImageController@getRecent');

Route::post('images/upload')
	->uses('Api\ImageController@upload')
    ->middleware('auth:api');

Route::put('image/{id}')
	->uses('Api\ImageController@update')
	->where('id', '\d+')
    ->middleware('auth:api');

Route::post('image/{id}/rating')
	->uses('Api\ImageController@submitRating')
	->where('id', '\d+')
    ->middleware('auth:api');

Route::get('user/images')
	->uses('Api\ImageController@getUserImages')
	->middleware('auth:api');

Route::get('tags')
	->uses('Api\TagController@getAllTags');

Route::get('tag/{slug}/images')
	->where('slug', '[\d\w\-]+')
	->uses('Api\TagController@getTagImages');
