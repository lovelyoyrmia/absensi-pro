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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });
        
        DB::table('settings')->insert([
            ['key' => 'work_start_time', 'value' => '08:00'],
            ['key' => 'work_limit_time', 'value' => '17:00']
        ]);

        DB::table('users')->insert([
            'name' => 'Owner Wanda',
            'email' => 'owner@example.com',
            'password' => bcrypt('ownerpassword'),
            'nip' => 'OWNER-001',
            'role' => 'owner',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
