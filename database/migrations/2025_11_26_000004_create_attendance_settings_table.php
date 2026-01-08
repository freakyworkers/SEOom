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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('setting_type'); // 'rank_points', 'consecutive_points', 'default_points', 'greeting', 'per_page'
            $table->string('setting_key'); // rank number, consecutive days, greeting text, etc.
            $table->text('setting_value'); // points amount, greeting text, etc.
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['site_id', 'setting_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};










