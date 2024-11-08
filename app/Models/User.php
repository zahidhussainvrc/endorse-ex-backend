<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Rating;
use App\Models\ExPartner;
use App\Models\Commit;
use App\Models\HeartConsumption;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password','api_token', 'referral_code','referred_by', 'hearts', 'profile_complete','facebook_connected','status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function exPartners()
    {
        return $this->hasMany(ExPartner::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function hearts()
    {
        return $this->hasMany(Heart::class);
    }

    public function heartConsumptions()
    {
        return $this->hasMany(HeartConsumption::class);
    }


    public function unlocks()
    {
        return $this->hasMany(ProfileUnlock::class);
    }

    public function commits()
    {
        return $this->hasMany(Commit::class);
    }


}
