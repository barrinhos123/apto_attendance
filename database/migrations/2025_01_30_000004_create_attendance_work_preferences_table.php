<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_work_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('expected_daily_hours', 5, 2)->default(Config::get('attendance.default_expected_daily_hours', 8));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_work_preferences');
    }
};
