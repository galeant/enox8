<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'can_access_admin',
        'can_access_customer',
        'can_access_super_admin',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $dates = ['deleted_at'];

    protected $appends = [
        'status',
        'fcm_token'
    ];

    public function validateForPassportPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }

    public function findForPassport($username)
    {
        return $user = (new User)->where('email', $username)->first();
    }

    public function detail()
    {
        return $this->hasOne('App\Models\V1\UserDetail', 'user_id', 'id');
    }

    public function socialLogin()
    {
        return $this->hasMany('App\Models\V1\SocialLogin', 'user_id', 'id');
    }

    public function comment()
    {
        return $this->hasMany('App\Models\V1\Comment', 'user_id', 'id');
    }

    public function reporting()
    {
        return $this->hasMany('App\Models\V1\Report', 'user_id', 'id');
    }

    public function reported()
    {
        return $this->hasMany('App\Models\V1\Report', 'relation_id', 'id')->where('relation_type', 'user');
    }
    // public function sendApiEmailVerificationNotification()
    // {
    //     $this->notify(new VerifyApiEmail); // my notification
    // }

    // public function linkedSocialAccounts()
    // {
    //     return $this->hasMany(LinkedSocialAccount::class);
    // }
    public function defaultAddress()
    {
        return $this->hasOne('App\Models\V1\Address', 'user_id', 'id')->where('main_address', true);
    }

    public function address()
    {
        return $this->hasMany('App\Models\V1\Address', 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->hasMany('App\Models\V1\Transaction', 'user_id', 'id');
    }

    public function cart()
    {
        return $this->hasMany('App\Models\V1\Cart', 'user_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo('App\Models\V1\Store', 'store_id', 'id');
    }

    public function wishlist()
    {
        return $this->belongsToMany('App\Models\V1\Type', 'user_wishlist', 'user_id', 'type_id')->withPivot('product_id');
    }

    // public function message(){
    //     return $this->belongsToMany('App\Models\V1\Store','message','user_id','store_id')->withPivot('contenct');
    // }

    // public function store_account(){
    //     return $this->hasOne('App\Models\V1\Store\Account','user_id','id');
    // }

    // public function detail(){
    //     if($this->role_id === 1){
    //         return $this->hasOne('App\Models\V1\Admin','user_id','id');
    //     }else{
    //         return $this->hasOne('App\Models\V1\UserDetail','user_id','id');
    //     }
    // }

    public function role()
    {
        return $this->belongsTo('App\Models\V1\Role', 'role_id', 'id');
    }

    // public function fcmToken()
    // {
    //     return $this->hasMany('App\Models\V1\FCM', 'user_id', 'id');
    // }

    public function getStatusAttribute()
    {
        $status = 'Active';
        if ($this->activation_token !== NULL || $this->activation_time_limit !== NULL) {
            $status = 'Activation required';
        } else if ($this->deleted_at !== NULL) {
            $status = 'Banned';
        }

        return $status;
    }

    public function getFcmTokenAttribute()
    {
        $return = [];
        $token = DB::table('oauth_access_tokens')->select('fcm_token')->where('user_id', $this->id)->get();
        if ($token->count() > 0) {
            $return = $token->pluck('fcm_token')->toArray();
        }
        return $return;
    }
}
