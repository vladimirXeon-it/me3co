<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'company',
        'email',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'zip'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
