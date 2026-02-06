<?php

declare(strict_types=1);

namespace Apto\Attendance\Models;

use App\Models\Business;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $table = 'attendance_schedules';

    protected $fillable = [
        'business_id',
        'name',
        'clock_in_time',
        'clock_out_time',
        'duration_minutes',
        'days_of_week',
        'location_type',
        'location',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'duration_minutes' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Compute duration in minutes from clock-in and clock-out times.
     * Handles overnight shifts: e.g. 22:00 to 06:00 = 8 hours.
     */
    public static function computeDurationMinutes(string $clockIn, string $clockOut): int
    {
        $in = self::timeToMinutes($clockIn);
        $out = self::timeToMinutes($clockOut);

        if ($out === $in) {
            return 24 * 60; // Same time = 24h shift
        }

        if ($out > $in) {
            return $out - $in;
        }

        // Overnight: e.g. 22:00 (1320) to 06:00 (360) = (1440 - 1320) + 360 = 480
        return (24 * 60 - $in) + $out;
    }

    protected static function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }

    /**
     * Format clock time for display (HH:mm).
     */
    public function getClockInFormattedAttribute(): string
    {
        $time = $this->clock_in_time;
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }
        return substr((string) $time, 0, 5);
    }

    public function getClockOutFormattedAttribute(): string
    {
        $time = $this->clock_out_time;
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }
        return substr((string) $time, 0, 5);
    }

    public const LOCATION_INSIDE = 'inside';
    public const LOCATION_OUTSIDE = 'outside';

    public static function locationTypes(): array
    {
        return [
            self::LOCATION_INSIDE  => __('No location'),
            self::LOCATION_OUTSIDE => __('Specific location'),
        ];
    }

    public function getLocationTypeLabelAttribute(): string
    {
        return self::locationTypes()[$this->location_type] ?? $this->location_type;
    }

    public static function weekdays(): array
    {
        return [
            'monday'    => __('Monday'),
            'tuesday'   => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday'  => __('Thursday'),
            'friday'    => __('Friday'),
            'saturday'  => __('Saturday'),
            'sunday'    => __('Sunday'),
        ];
    }

    public function isInside(): bool
    {
        return $this->location_type === self::LOCATION_INSIDE;
    }

    public function isOutside(): bool
    {
        return $this->location_type === self::LOCATION_OUTSIDE;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
