<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrawingTemplate extends Model
{
    use HasFactory;
    
    protected $table = 'drawing_template';

    protected $fillable = [
        'user_id',
        'template_id',
        'template_name',
        'color',
    ];
}