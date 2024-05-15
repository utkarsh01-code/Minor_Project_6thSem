<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwitterSecretToken extends Model
{
    use HasFactory;
    protected $fillable=[
    "user_id",
    "twitter_id",
    "token_secret",
    "nickname"];

    // public function SocialAccount(){
    //     return $this->belongsTo('/App/Models/SocialAccount');
    // }
}
