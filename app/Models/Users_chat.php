<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users_chat extends Model
{
    use HasFactory;

    protected $table = 'users_chats';
    
    protected $fillable = [
        'users_id1',
        'users_id2',
    ];

    public function chattingUser()
    {
        return $this->belongsTo(User::class, 'users_id1');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'users_id2');
    }
}
