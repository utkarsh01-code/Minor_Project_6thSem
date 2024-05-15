<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\SocialAccount;
use Auth;
use Log;
use GuzzleHttp;

class LinkedIncontroller extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }
    /**
     * Redirect the user to the linkedin authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        $url= Socialite::with('linkedin')
        ->scopes(["w_member_social",'r_organization_social','r_1st_connections_size','rw_organization_admin','w_organization_social'])
        ->stateless()
        ->redirect()
        ->getTargetUrl();
        return response()->json([
            'url' => $url
        ], 200);    }

    /**
     * Obtain the user information from linkedin.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        // try {
        $Liaccount=SocialAccount::where(['user_id'=>Auth::id(),'type'=>'2'])->latest('created_at')->first();
        if($Liaccount){
            $response = Http::get("https://api.linkedin.com/v2/organizationalEntityAcls?q=roleAssignee&oauth2_access_token=".$Liaccount->accessToken);
            $response=$response->json();
            $org_ids=$response['elements'];
            $Liaccount->org_ids=$org_ids;
            return response()->json([
                'user'=>$Liaccount
            ]);
        }
            $user = Socialite::with('linkedin')->stateless()->user();

            $user=SocialAccount::create([
                "user_id"=>Auth::id(),
                "email"=>$user->email,
                "name"=>$user->name,
                "SocialUserID"=>$user->id,
                "type"=>'2',
                "profile_pic"=>$user->avatar,
                "data_access_expiration_time"=>date('Y-m-d H:i:s',$user->expiresIn),
                "accessToken"=>$user->token,
            ]);
            return response()->json($user);

    }
    public function getLiCreds(){
        $Liaccount=SocialAccount::where(['user_id'=>Auth::id(),'type'=>'2'])->latest('created_at')->first();
        if($Liaccount){
            $response = Http::get("https://api.linkedin.com/v2/organizationalEntityAcls?q=roleAssignee&oauth2_access_token=".$Liaccount->accessToken);
            $response=$response->json();
            $org_ids=$response['elements'];
            $Liaccount->org_ids=$org_ids;
            return response()->json([
                'Li'=>true,
                'user'=>$Liaccount
            ]);
        }
        return response()->json([
            'Li'=>false,
        ]);
    }
    public function PostOnLinkedIn(Request $request){
        $Liaccount=SocialAccount::where(['user_id'=>Auth::id(),'type'=>'2'])->latest('created_at')->first();
        $pic=[
            "registerUploadRequest"=> [
                "recipes"=> [
                    "urn:li:digitalmediaRecipe:feedshare-image"
                ],
                "owner"=> "urn:li:person:".$Liaccount['SocialUserID'],
                "serviceRelationships"=> [
                    [
                        "relationshipType"=> "OWNER",
                        "identifier"=> "urn:li:userGeneratedContent"
                   ]
                ]
           ]
                    ];
        $linkedin = [
            'author'    => "urn:li:person:".$Liaccount['SocialUserID'],
            'lifecycleState'=> "PUBLISHED",
            'specificContent'=> [
                "com.linkedin.ugc.ShareContent"=> [
                    'shareCommentary'=> [
                        'text'=> $request->text,
                    ],
                    'shareMediaCategory'=> "NONE"
                ]
            ],
            'visibility'=> [
                "com.linkedin.ugc.MemberNetworkVisibility"=> "PUBLIC"
            ]
        ];
        $response = Http::withBody(
            json_encode($pic),'application/json'
        )->withHeaders(["X-Restli-Protocol-Version"=> "2.0.0"])->post('https://api.linkedin.com/v2/assets?action=registerUpload&oauth2_access_token='.$Liaccount['accessToken']
        );
        $response=$response->json();
        $url=$response['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
        $asset=$response['value']['asset'];
        if($request->file('source')){
            $path = $request->file('source')->store(
                Auth::id(), 'public'
            );
            $file    = fopen(storage_path()."/app/public/".$path, 'r');
            $size    = filesize(storage_path()."/app/public/".$path);
            $fildata = fread($file, $size);
            $f=$request->file('source');
            $postfield = array("upload-file" => $request->file('source'));
            $ext=$request->file('source')->getClientOriginalExtension();
            $headers = array();
            $headers[] = 'Authorization: Bearer '.$Liaccount['accessToken'];// token generated above code
            $headers[] = 'X-Restli-Protocol-Version: 2.0.0';
            $headers[] = 'Content-Type: image/'.$request->file('source')->getClientOriginalExtension();
            $ch = curl_init();
            $options = array(
                CURLOPT_HEADER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
                // CURLOPT_SAFE_UPLOAD => false,
                CURLOPT_POSTFIELDS => $fildata
            );
            curl_setopt_array($ch, $options);
            $imgResponse = curl_exec($ch);
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
            }
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        $photo=[
        "author"=> "urn:li:person:".$Liaccount['SocialUserID'],
        "lifecycleState"=> "PUBLISHED",
        "specificContent"=> [
            "com.linkedin.ugc.ShareContent"=> [
                "shareCommentary"=> [
                    "text"=> $request->text,
                ],
                "shareMediaCategory"=> "IMAGE",
                "media"=> [
                    [
                        "status"=> "READY",
                        "description"=> [
                            "text"=> $request->text,
                        ],
                        "media"=> $asset,
                        "title"=> [
                            "text"=> $request->text,
                        ]
                    ]
                ]
            ]
        ],
        "visibility"=> [
            "com.linkedin.ugc.MemberNetworkVisibility"=> "PUBLIC"
        ]
        ];
        }
        else{
            $photo = [
                'author'    => "urn:li:person:".$Liaccount['SocialUserID'],
                'lifecycleState'=> "PUBLISHED",
                'specificContent'=> [
                    "com.linkedin.ugc.ShareContent"=> [
                        'shareCommentary'=> [
                            'text'=> $request->text,
                        ],
                        'shareMediaCategory'=> "NONE"
                    ]
                ],
                'visibility'=> [
                    "com.linkedin.ugc.MemberNetworkVisibility"=> "PUBLIC"
                ]
            ];
        }
        $response = Http::withBody(
            json_encode($photo),'application/json'
        )->withHeaders(["X-Restli-Protocol-Version"=> "2.0.0"])->post('https://api.linkedin.com/v2/ugcPosts?oauth2_access_token='.$Liaccount['accessToken'] // [json_encode('{"linkedin":json_encode($request->linkedin)}')]
    );
        return $response;
    }

    public function getlinkedInFollowerData(Request $request){
        $apis=explode(',',$request->apiReqs);
        $response = Http::get($apis[0]);
        $return_arr=[];
        $response=$response->json();
        $return_arr['total_followers']=[$response];
        $response = Http::get($apis[1]);
        $response=$response->json();
        $return_arr['week_followers']=[$response];
        return response()->json($return_arr);
    }
    public function getlinkedInPostData(Request $request){
        $response = Http::get($request->apiReqs);
        $response=$response->json();
        $return_arr['total_posts']=$response['paging']['total'];
        return response()->json($return_arr);
    }
    public function getlinkedInViewerData(Request $request){
        $response = Http::get($request->apiReqs);
        $response=$response->json();
        return $response;
    }

}
