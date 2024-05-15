<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\SocialAccount;
use App\Models\FbPage;
use Auth;
use Log;
class FacebookController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }
    /**
     * Redirect the user to the facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        $url= Socialite::driver('facebook')
        ->scopes(["public_profile","pages_read_engagement"])
        // ,"pages_show_list","pages_manage_metadata","pages_manage_posts"])
        ->stateless()
        ->redirect()
        ->getTargetUrl();
        return response()->json([
            'url' => $url
        ], 200);
    }

    /**
     * Obtain the user information from facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        // try {
            $fbaccount=SocialAccount::with(['FbPages'=> function($q){
            $q->where('user_id',Auth::id());
        }])->where(['user_id'=>Auth::id(),'type'=>'0'])->latest('created_at')->first();
            if($fbaccount)
                return response()->json(['user'=>$fbaccount]);

            $user = Socialite::driver('facebook')->stateless()->user();
            $user=SocialAccount::create([
                "user_id"=>Auth::id(),
                "email"=>$user->email,
                "name"=>$user->name,
                "SocialUserID"=>$user->id,
                "type"=>'0',
                "profile_pic"=>'pppp',
                "data_access_expiration_time"=>date('Y-m-d H:i:s'),
                "accessToken"=>$user->token,
            ]);
            // return response()->json($user);

            $response = Http::get("https://graph.facebook.com/".$user->SocialUserID."/accounts?access_token=".$user->accessToken);
            $response=$response->json();
            // Log::info($response);
            foreach ($response['data'] as $page) {
                FbPage::create([
                    'user_id'=>Auth::id(),
                    'page_id'=>$page['id'],
                    'fb_id'=>$user['SocialUserID'],
                    'name'=>$page['name'],
                    'accessToken'=>$page['access_token']
                ]);
            }
            $user['pages']=$response;
            $fbaccount=SocialAccount::with(['FbPages'=> function($q){
            $q->where('user_id',Auth::id());
        }])->where(['user_id'=>Auth::id(),'type'=>'0'])->latest('created_at')->first();

            return response()->json(['user'=>$fbaccount]);
            // return redirect('#/facebookSuccess');
        // }
        // catch (\Throwable $th) {
            // return redirect('#/fberror');
        // }

    }
    public function getFBCreds(){
        $fbaccount=SocialAccount::with(['FbPages'=> function($q){
            $q->where('user_id',Auth::id());
        }])->where(['user_id'=>Auth::id(),'type'=>'0'])->latest('created_at')->first();
        // dd($fbaccount);
        if($fbaccount){
            return response()->json([
                'fb'=>true,
                'user'=>$fbaccount
            ]);
        }

        return response()->json([
            'fb'=>FALSE
        ]);
    }
    public function addFbPages(Request $request){

    }
}
