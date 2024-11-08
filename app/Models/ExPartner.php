<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Rating;
use App\Models\Commit;


class ExPartner extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'full_name', 'age_range', 'birthday', 'gender', 'relationship_duration',
        'college', 'email', 'address', 'phone_number', 'profession', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }


     // One-to-Many relationship with Commit
    //  public function comments()
    //  {
    //      return $this->hasMany(Commit::class);
    //  }

    public function comments()
    {
        return $this->hasMany(Commit::class)->active(); // Use the active scope
    }



}
