<?php

declare(strict_types=1);

namespace Apto\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Apto\Attendance\Models\Schedule;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'user_id',
        'shift_id',
        'schedule_id',
        'clock_in_at',
        'clock_out_at',
        'status',
        'notes',
        'location',
        'gps_coordinates',
        'latitude',
        'longitude',
        'location_permission_denied',
    ];

    protected $casts = [
        'location_permission_denied' => 'boolean',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(AttendanceShift::class, 'shift_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AttendanceEvent::class, 'attendance_record_id');
    }

    /** Total break time in seconds. */
    public function getTotalBreakSecondsAttribute(): int
    {
        $events = $this->events()->orderBy('occurred_at')->get();
        $total = 0;
        $breakStartAt = null;

        foreach ($events as $event) {
            if ($event->type === 'break_start') {
                $breakStartAt = $event->occurred_at;
            } elseif ($event->type === 'break_end' && $breakStartAt) {
                $total += $event->occurred_at->getTimestamp() - $breakStartAt->getTimestamp();
                $breakStartAt = null;
            }
        }

        return (int) $total;
    }

    /** Total work time in seconds (clock out minus clock in). */
    public function getTotalWorkSecondsAttribute(): int
    {
        if (! $this->clock_in_at) {
            return 0;
        }

        $end = $this->clock_out_at ?? now();
        $seconds = $end->getTimestamp() - $this->clock_in_at->getTimestamp();

        return (int) max(0, $seconds);
    }

    /** First line of notes for compact display (strips HTML, one line only). */
    public function getNotesFirstLineAttribute(): ?string
    {
        if (! $this->notes) {
            return null;
        }

        $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $this->notes));
        $lines = preg_split('/\r\n|\r|\n/', $text, 2);
        $first = trim($lines[0] ?? '');

        return $first === '' ? null : $first;
    }

    /** Human-readable duration, e.g. "2h 30m". */
    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }
        $mins = (int) floor($seconds / 60);
        $secs = $seconds % 60;
        if ($mins < 60) {
            return $secs > 0 ? "{$mins}m {$secs}s" : "{$mins}m";
        }
        $hours = (int) floor($mins / 60);
        $mins = $mins % 60;

        return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
    }
}
