<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('shift_id')->constrained('attendance_schedules')->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable()->after('gps_coordinates');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        Schema::table('attendance_events', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('gps_coordinates');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('attendance_events', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
