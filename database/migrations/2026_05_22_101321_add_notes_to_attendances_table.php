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
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('status', ['masuk', 'sakit', 'izin', 'cuti'])->default('masuk');
            $table->enum('approval_status', ['approved', 'pending', 'rejected'])->default('approved');
            $table->text('notes')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
            $table->dropColumn(['status', 'approval_status', 'notes']);
        });
    }
};
