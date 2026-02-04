<?php

declare(strict_types=1);

namespace Apto\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AttendanceEvent extends Model
{
    use HasFactory;

    protected $table = 'attendance_events';

    protected $fillable = [
        'attendance_record_id',
        'user_id',
        'type',
        'occurred_at',
        'notes',
        'location',
        'gps_coordinates',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
