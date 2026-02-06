<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_schedules', function (Blueprint $table) {
            $table->time('clock_in_time')->default('09:00')->after('duration_minutes');
            $table->time('clock_out_time')->default('17:00')->after('clock_in_time');
        });

        // Migrate existing duration_minutes to clock times (default: 09:00 start)
        $schedules = DB::table('attendance_schedules')->get();
        foreach ($schedules as $schedule) {
            $startMinutes = 9 * 60; // 09:00
            $endMinutes = $startMinutes + $schedule->duration_minutes;
            if ($endMinutes >= 24 * 60) {
                $endMinutes -= 24 * 60; // Overnight: e.g. 09:00 + 20h = 05:00 next day
            }
            $clockIn = sprintf('%02d:%02d:00', (int) floor($startMinutes / 60), $startMinutes % 60);
            $clockOut = sprintf('%02d:%02d:00', (int) floor($endMinutes / 60), $endMinutes % 60);

            DB::table('attendance_schedules')->where('id', $schedule->id)->update([
                'clock_in_time'  => $clockIn,
                'clock_out_time' => $clockOut,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('attendance_schedules', function (Blueprint $table) {
            $table->dropColumn(['clock_in_time', 'clock_out_time']);
        });
    }
};
