<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    public function material_class() {
        return $this->hasMany(MaterialClass::class);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($material_division) { // before delete() method call this
             $material_division->materials()->delete();
             $material_division->material_class()->delete();
             // do the rest of the cleanup...
        });
    }
}
