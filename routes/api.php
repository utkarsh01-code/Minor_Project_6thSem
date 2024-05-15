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
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\SocialMediaAccountController;
use \App\Http\Controllers\FacebookController;
use \App\Http\Controllers\TwitterController;
use \App\Http\Controllers\LinkedInController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class,'login']);
    Route::post('signup', [AuthController::class,'signup']);
    Route::get('logout', [AuthController::class,'logout']);
    Route::get('user', [AuthController::class,'user']);
});


// Route::post('addSocialAccount/facebook',[SocialMediaAccountController::class,'addFacebookAccount'])->name("social.fb.add");
Route::get('/FBCreds',[FacebookController::class,'getFBCreds'])->name("social.fb.get");
Route::get('/LiCreds',[LinkedInController::class,'getLiCreds'])->name("social.li.get");
Route::get('/TwCreds',[TwitterController::class,'getTwCreds'])->name("social.tw.get");

//linkedIN routes
Route::get('login/twitter', [TwitterController::class, 'redirectToProvider']);
Route::get('login/facebook', [FacebookController::class, 'redirectToProvider']);
Route::get('login/linkedin', [LinkedInController::class, 'redirectToProvider']);
Route::post('postlinkedin', [LinkedInController::class, 'PostOnLinkedIn']);
Route::post('getlinkedInFollowerData', [LinkedInController::class, 'getlinkedInFollowerData']);
Route::post('getlinkedInPostData', [LinkedInController::class, 'getlinkedInPostData']);
Route::post('getlinkedInViewerData', [LinkedInController::class, 'getlinkedInViewerData']);

Route::get('/twitter', [TwitterController::class, 'handleProviderCallback']);
Route::get('/linkedin', [LinkedInController::class, 'handleProviderCallback']);
Route::get('/facebook', [FacebookController::class, 'handleProviderCallback']);
// Route::view('/{path?}', 'welcome');
Route::post('twitter/post',[TwitterController::class, 'tweet']);


Route::post('/test',function(Request $request){
    $text=$request->get('text');
    $data=json_encode([
        "text"=> $text,
        "features"=> [
          "sentiment"=> [
            'document'=>true
            ],
            "keywords"=> [
                "sentiment"=> true,
                "emotion"=> true,
            ]
        ]
          ]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.eu-gb.natural-language-understanding.watson.cloud.ibm.com/instances/03bcb6b1-7bed-4f89-93d9-80ed888a7920/v1/analyze?version=2019-07-12");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
));
    curl_setopt($ch, CURLOPT_USERPWD, "apikey:xmV5QvEhWipvsUHbxT6XixH9EO-bNHyodqNotygGRMoF");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return response()->json($output);
});

