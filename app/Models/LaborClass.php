<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborClass extends Model
{
    use HasFactory;

    protected $fillable = [
        "name"
    ];

    public function labors()
    {
        return $this->hasMany(Labor::class);
    }
    public function openings() {
        return $this->hasMany(Opening::class);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($labor_class) { // before delete() method call this
             $labor_class->labors()->delete();
             $labor_class->openings()->delete();
             // do the rest of the cleanup...
        });
    }
}
