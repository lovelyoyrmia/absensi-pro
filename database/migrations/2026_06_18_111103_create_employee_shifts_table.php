<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('shift_name'); // Shift 1, Shift 2, Shift 3
            $table->time('start_time');   // 07:00, 15:00, 23:00
            $table->time('end_time');     // 15:00, 23:00, 07:00
            $table->timestamps();
            
            $table->unique(['user_id', 'date']); // Satu karyawan hanya punya 1 shift per hari
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};
