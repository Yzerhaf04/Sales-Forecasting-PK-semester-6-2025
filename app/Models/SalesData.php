<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesData extends Model
{
    protected $table = 'sales_data';
    protected $guarded = [];

    public $timestamps = true;

    protected $fillable = [
        'store',
        'dept',
        'date',
        'weekly_sales',
    ];

    protected $casts = [
        'date' => 'date',
        'weekly_sales' => 'float',
        'store' => 'integer',
        'dept' => 'integer',
    ];
}
