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
            $table->string('shift_name')->nullable()->after('user_id'); // Shift 1, Shift 2, Shift 3, Pagi
            $table->time('shift_start')->nullable()->after('shift_name');
            $table->time('shift_end')->nullable()->after('shift_start');
            $table->text('late_reason')->nullable()->after('is_late');
            $table->string('late_proof')->nullable()->after('late_reason'); // Path foto bukti telat
        });
        
        if (!Schema::hasColumn('users', 'division')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('division')->default('CS')->after('role'); // Finance, CS, Admin
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
