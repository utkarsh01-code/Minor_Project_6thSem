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
use App\Http\Controllers\TwitterController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\LinkedInController;
// use Input;
// use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});

Route::view('/{path?}/{code?}', 'welcome')->name('linked');
Route::view('/{path?}', 'welcome');
