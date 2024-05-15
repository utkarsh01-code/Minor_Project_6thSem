<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\SocialAccount;
use App\Models\TwitterSecretToken;
use Auth;
use Log;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }
    /**
     * Redirect the user to the twitter authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        // session_start();
        $url =Socialite::driver('twitter')
        ->redirect()->getTargetUrl();
        return response()->json([
            'url' => $url
        ], 200);
    }

    /**
     * Obtain the user information from twitter.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request)
    {
        // try {
            // dd(1);
            // $content = $connection->get("account/verify_credentials");
            // $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $request->get('oauth_verifier')]);
            // $statues = $connection->post("statuses/update", ["status" => "hello world"]);
            $user = Socialite::driver('twitter')->userFromTokenAndSecret("876693859371286528-WEw3LzrpUTu139iqHJho0WQvDDZuwqw",'jiNtbvsS9vHD3U3QFv22DRjw3Ikv6PsghpOgInG3AFvFX');
            // dd($user);
            // return response()->json($user);
            // dd($user);
            $user1=SocialAccount::create([
                "user_id"=>Auth::id(),
                "email"=>$user->email,
                "name"=>$user->name,
                "SocialUserID"=>$user->id,
                "type"=>'3',
                "profile_pic"=>$user->avatar,
                "data_access_expiration_time"=>date('Y-m-d H:i:s'),
                "accessToken"=>$user->token,
            ]);
            $usert=TwitterSecretToken::create([
                "user_id"=>Auth::id(),
                "twitter_id"=>$user->id,
                'token_secret'=>$user->tokenSecret,
                'nickname'=>$user->nickname
            ]);
            foreach ($usert as $key => $value) {
                $user1[$key]=$value;
            }
            return response()->json($user1);

            // return redirect('#/twitterSuccess');
        // }
        // catch (\Throwable $th) {
            // return redirect('#/twittererror');
        // }

    }
    public function getTwCreds(){
        $TWaccount=SocialAccount::where(['user_id'=>Auth::id(),'type'=>'3'])->latest('created_at')->first();
        $tw=TwitterSecretToken::where(['user_id'=>Auth::id(),'twitter_id'=>$TWaccount['SocialUserID']])->latest('created_at')->first();
        if($TWaccount){
            $TWaccount['nickname']=($TWaccount)?($tw->nickname):$tw->name;
            return response()->json([
                'Tw'=>true,
                'user'=>$TWaccount
            ]);
        }
        return response()->json([
            'Tw'=>FALSE
        ]);
    }

    public function tweet(Request $request){
        $connection = new TwitterOAuth(env('TWITTER_CLIENT_ID'), env('TWITTER_CLIENT_SECRET'), $request->get('token'), $request->get('tokenSecret'));
        $file=$request->file('source');
        $media1 = $connection->upload('media/upload', ['media' => strval($file)]);
        dd($media1);
        Log::info(json_encode($media1));
        $parameters = [
            'status' => $request->get('message'),
            'media_ids' =>  $media1->media_id_string
        ];
        $result = $connection->post('statuses/update', $parameters);
        return response()->json(200);

    }
}
