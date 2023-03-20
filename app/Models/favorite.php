<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $table = 'favorites';
    
    protected $fillable = [
        'favorating',
        'favorated',
    ];

    public function blockingUser()
    {
        return $this->belongsTo(User::class, 'favorating');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'favorated');
    }
}
