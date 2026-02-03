<?php

declare(strict_types=1);

namespace Apto\Attendance\Events;

use Apto\Attendance\Models\AttendanceRecord;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public AttendanceRecord $attendance)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('attendance');
    }
}
