<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    use HasFactory;
    protected $table = 'user_invitations';
    protected $fillable = 
    [
     'project_id',   
     'invitation_token',   
     'to_invite',   
    ];
}