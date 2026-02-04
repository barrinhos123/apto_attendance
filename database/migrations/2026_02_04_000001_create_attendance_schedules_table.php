<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('duration_minutes');
            $table->json('days_of_week'); // e.g. ["monday", "tuesday"]
            $table->string('location_type', 20); // 'inside' | 'outside'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_schedules');
    }
};
