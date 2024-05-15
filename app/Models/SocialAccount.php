<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;
    protected $fillable=[
    "user_id",
    "name",
    "email",
    "SocialUserID",
    "type",
    "profile_pic",
    "data_access_expiration_time",
    "accessToken",
    ];

    public function User(){
        return $this->belongsTo('App\Models\User');
    }
    public function FbPages(){
        return $this->hasMany('App\Models\FbPage','fb_id','SocialUserID');
    }
}
