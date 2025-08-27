<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'directory',
        'local_db'
    ];

    protected $casts = [
        'local_db' => 'array',       
    ];
}
