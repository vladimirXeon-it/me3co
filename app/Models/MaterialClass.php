<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialClass extends Model
{
    use HasFactory;
    protected $fillable = [
        'material_division_id',
        'name'
    ];

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    public function material_division() {
        return $this->belongsTo(MaterialDivision::class);
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($material_class) { // before delete() method call this
             $material_class->materials()->delete();
             // do the rest of the cleanup...
        });
    }
}
