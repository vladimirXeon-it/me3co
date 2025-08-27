<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opening extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'description',
        'labor_class_id',
        'labor_id',
        'opening_shape_id',
        'measurement_unit',
        'length',
        'height',
        'elevation',
        'header',
        'bearing',
        'materials',
        'caulking'
    ];

    public function labor_class() {
        return $this->belongsTo(LaborClass::class);
    }
    public function labor() {
        return $this->belongsTo(Labor::class);
    }
    public function opening_shape() {
        return $this->belongsTo(OpeningShape::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
