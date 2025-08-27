<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_intent',
        'plan_id',
        'price',
        'status'
    ];

    public function plan() {
        return $this->belongsTo(Plan::class);
    }
}
