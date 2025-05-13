<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesData extends Model
{
    protected $fillable = [
        'store',
        'department',
        'date',
        'sales',
    ];
    
    public $timestamps = false;

    protected $casts = [
        'date' => 'date',
        'weekly_sales' => 'float',
    ];
}
