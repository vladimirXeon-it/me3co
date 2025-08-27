<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project_old extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'estimator',
        'address',
        'country',
        'state',
        'city',
        'zip',
        'customer',
        'bid_number',
        'bid_date',
        'bid_time',
        'materials',
        'crews',
        'equipments',
        'items',
        'weather_adjustment',
        'other_equipment',
        'other_labor',
        'material_profit_info',
        'labor_profit_info',
        'equipment_profit_info',
        'subcontractor_profit_info',
        'other_profit_info'
    ];
    public function openings() {
        return $this->hasMany(Opening::class);
    }
    public function document() {
        return $this->hasOne(Document::class);
    }
    public function projectCustomer() {
        return $this->hasOne(Contact::class, "id", "customer");
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($project) {
            $project->openings()->delete();
            $project->document()->delete();
        });
    }
}
