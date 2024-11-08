<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\ExPartner;
use App\Models\User;
use App\Models\CustomTrait;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'ex_partner_id',
        'user_id',
        'trait_id',
        'rating',
        'secret_message',
        'approved',
    ];

    protected $casts = [
        'traits' => 'array'
    ];

    public function exPartner()
    {
        return $this->belongsTo(ExPartner::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function trait()
    {
        return $this->belongsTo(CustomTrait::class);
    }
}
