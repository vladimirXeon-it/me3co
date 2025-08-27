<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        'user_id',
        "description",
        "unique_id",
        "cost_per_day",
        'project_id',
    ];
    public function crews()
    {
        return $this->hasMany(Crew::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
