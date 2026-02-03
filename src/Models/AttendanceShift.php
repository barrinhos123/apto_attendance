<?php

declare(strict_types=1);

namespace Apto\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class AttendanceShift extends Model
{
    use HasFactory;

    protected $table = 'attendance_shifts';

    protected $fillable = [
        'user_id',
        'scheduled_start',
        'scheduled_end',
        'expected_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'shift_id');
    }
}
