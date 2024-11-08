<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Heart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'hearts', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addHearts($amount)
    {
        $this->hearts += $amount;
        $this->status = true; // Set status as active/earned
        $this->save();
    }

    public function spendHearts($amount)
    {
        if ($this->hearts >= $amount) {
            $this->hearts -= $amount;


            // Check if hearts have reached zero and update the status accordingly
            if ($this->hearts <= 0) {
                $this->hearts = 0; // Ensure hearts do not go negative
                $this->status = 'spent'; // Set status as inactive
            }

            $this->save();
            return true; // Hearts spent successfully
        }

        return false; // Not enough hearts
    }
}
