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
            $table->string('gps_coordinates')->nullable()->after('location');
        });

        Schema::table('attendance_events', function (Blueprint $table) {
            $table->string('gps_coordinates')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('gps_coordinates');
        });

        Schema::table('attendance_events', function (Blueprint $table) {
            $table->dropColumn('gps_coordinates');
        });
    }
};
