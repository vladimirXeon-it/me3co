<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
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
        'other_profit_info',
        'job_walk_date',
        'job_walk_time',
        'rfi_due_date',
        'rfi_due_time',
        'start_date',
        'end_date',
        'size',
        'measurement_unit',
        'architect',
        'description',
        'competitive_bidding',
        'project_type',
        'budgeting_type',
        'client',
        'bid_to_client_date',
        'bid_to_client_time',
        'account_manager',
        'project_value',
        'fee_percentage',
        'market_sector',
        'certificate',
        'certifying_agency',
        'notes',
        'owning_office',
        'scope',
        'folder',
        'files'
    ];
    function invitedUser(){
        return $this->hasMany(InviteProject::class, 'project_id');
    }
    public function openings()
    {
        return $this->hasMany(Opening::class);
    }
    public function document()
    {
        return $this->hasOne(Document::class);
    }
    public function projectCustomer()
    {
        return $this->hasOne(Contact::class, "id", "customer");
    }
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($project) {
            $project->openings()->delete();
            $project->document()->delete();
        });
    }
    public function labors()
    {
        return $this->hasMany(Labor::class);
    }
    public function crews()
    {
        return $this->hasMany(Crew::class);
    }
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
}
