<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_record_id')->constrained('attendance_records')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->dateTime('occurred_at');
            $table->text('notes')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['attendance_record_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
