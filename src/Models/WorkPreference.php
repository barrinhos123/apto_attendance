<?php

declare(strict_types=1);

namespace Apto\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class WorkPreference extends Model
{
    use HasFactory;

    protected $table = 'attendance_work_preferences';

    protected $fillable = [
        'user_id',
        'expected_daily_hours',
    ];

    protected $casts = [
        'expected_daily_hours' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
