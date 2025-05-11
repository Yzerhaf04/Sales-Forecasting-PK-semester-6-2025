<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesData extends Model
{
    protected $table = 'sales_data';
    public $timestamps = false;

    protected $casts = [
        'date' => 'date',
        'weekly_sales' => 'float',
    ];
}
