<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'shift_name',
        'start_time',
        'end_time',
    ];

    // Relasi balik ke data User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}