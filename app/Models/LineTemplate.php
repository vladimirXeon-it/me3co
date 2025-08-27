<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineTemplate extends Model
{
    use HasFactory;
    protected $table = 'line_templates';
    protected $fillable = ['user_id', 'template_name', 'trade_name', 'local_db'];
}