<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentimenData extends Model
{
    protected $table = 'sentimen_data';

    public $timestamps = false;

    protected $fillable = [
        'review_text',
        'label_sentimen',
    ];
}
