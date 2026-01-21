<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'holidays',
        'saturday_holiday_count',
        'synced_at'
    ];

    protected $casts = [
        'holidays' => 'array',
        'synced_at' => 'datetime'
    ];
}
