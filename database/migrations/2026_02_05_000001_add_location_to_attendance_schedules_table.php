<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_schedules', function (Blueprint $table) {
            $table->string('location')->nullable()->after('location_type');
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_schedules', function (Blueprint $table) {
            $table->dropColumn(['location', 'latitude', 'longitude']);
        });
    }
};
