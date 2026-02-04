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

    public const LOCATION_INSIDE = 'inside';
    public const LOCATION_OUTSIDE = 'outside';

    public static function locationTypes(): array
    {
        return [
            self::LOCATION_INSIDE  => __('Inside'),
            self::LOCATION_OUTSIDE => __('Outside'),
        ];
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
