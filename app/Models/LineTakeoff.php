<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineTakeoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'company',
        'email',
        'phone',
        'address',
        'country',
        'state',
        'city',
        'zip'
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
