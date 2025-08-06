<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'status_point', // 0 for machine + , 1 for reward -
        'weight',
        'point',
    ];
}
