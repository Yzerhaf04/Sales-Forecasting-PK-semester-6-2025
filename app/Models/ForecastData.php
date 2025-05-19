<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForecastData extends Model
{
    protected $table = 'forecast_sales';

    protected $fillable = [
        'dept_id',
        'period',
        'forecast_date',
        'forecast_sales',
    ];

    public $timestamps = true;

    protected $casts = [
        'dept_id' => 'integer',
        'period' => 'string',
        'forecast_date' => 'datetime',
        'forecast_sales' => 'float',
    ];
}
