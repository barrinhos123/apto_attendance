<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('attendance_shifts')->nullOnDelete();
            $table->dateTime('clock_in_at')->nullable();
            $table->dateTime('clock_out_at')->nullable();
            $table->string('status')->default('in_progress');
            $table->text('notes')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('clock_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
