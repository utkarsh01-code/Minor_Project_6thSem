<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class FbPage extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function SocialAccount(){
        return $this->belongsTo('App\Models\SocialAccount','fb_id','SocialUserID');
    }
}
