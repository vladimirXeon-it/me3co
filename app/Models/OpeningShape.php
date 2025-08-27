<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpeningShape extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function openings() {
        return $this->hasMany(Opening::class);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($opening_shape) { // before delete() method call this
             $opening_shape->openings()->delete();
             // do the rest of the cleanup...
        });
    }
}
