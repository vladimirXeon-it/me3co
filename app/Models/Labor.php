<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'labor_class_id',
        'labor_type',
        'unique_id',
        'cost_per_hour',
        'burdens',
        'total_cost',
        'project_id',
    ];

    public function labor_class() {
        return $this->belongsTo(LaborClass::class);
    }

    public function openings()
    {
        return $this->hasMany(Opening::class);
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($labors) { // before delete() method call this
             $labors->openings()->delete();
             // do the rest of the cleanup...
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
