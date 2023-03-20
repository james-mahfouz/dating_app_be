<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $table = 'block';
    
    protected $fillable = [
        'blocking',
        'blocked',
    ];

    public function blockingUser()
    {
        return $this->belongsTo(User::class, 'blocking');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked');
    }
}

