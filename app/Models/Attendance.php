<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'is_late',
        'address'
    ];

    // 2. Tell Laravel to treat these as Date objects (Carbon)
    // This allows you to do $attendance->clock_in->format('H:i')
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'is_late' => 'boolean',
        'address' => 'string'
    ];

    /**
     * Relationship: Get the user that owns the attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Quickly filter records that are late.
     * Usage: Attendance::late()->get();
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}
