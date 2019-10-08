<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\EmailVerification;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable,SoftDeletes;


    protected $fillable = [
        'name', 'email', 'username', 'password', 'type', 'photo_url',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class,'responsible_cook_id');
    }

    public function sendEmailVerificationNotification() {
        $this->notify(new EmailVerification($this->id));
    }


    public function meals()
    {
        return $this->hasMany(Meal::class,'responsible_waiter_id');
    }
}
