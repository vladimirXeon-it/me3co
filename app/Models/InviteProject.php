<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteProject extends Model
{
    use HasFactory;
    protected $table = 'invite_projects';
    protected $fillable = [
      'project_id',  
      'invite_to',  
      'status',  
    ];
    
}