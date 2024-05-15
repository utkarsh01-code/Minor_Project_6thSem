<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialAccount;
use Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Date;
use Carbon\Carbon;
use DateTime;


class SocialMediaAccountController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }
    public function addFacebookAccount(Request $request){
        SocialAccount::insert([
            "user_id"=>Auth::id(),
            "email"=>$request->email,
            "name"=>$request->name,
            "SocialUserID"=>$request->id,
            "type"=>'0',
            "profile_pic"=>$request['picture']['data']['url'],
            "data_access_expiration_time"=>date('Y-m-d H:i:s', $request->data_access_expiration_time),
            "accessToken"=>$request->accessToken,
        ],['SocialUserID'],['data_access_expiration_time','accessToken']);
         return response()->json([
            'message' => 'Successfully created FBaccount!',
            "accessToken"=>$request->accessToken,
            "id"=>$request->id
        ], 201);
    }
}
