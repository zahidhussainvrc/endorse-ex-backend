<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ExPartner;
class Commit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'ex_partner_id', 'message', 'status'
    ];

    // Inverse One-to-Many relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Inverse One-to-Many relationship with ExPartner
    public function exPartner()
    {
        return $this->belongsTo(ExPartner::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

}
