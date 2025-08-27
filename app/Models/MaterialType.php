<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    use HasFactory;
    protected $fillable = [
        
        'name'
    ];

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    

    public static function boot() {
        parent::boot();

        static::deleting(function($material_type) { // before delete() method call this
             $material_type->materials()->delete();
             // do the rest of the cleanup...
        });
    }
}
