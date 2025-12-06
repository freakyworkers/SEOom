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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->date('visited_date');
            $table->timestamps();
            
            // 인덱스 추가
            $table->index(['site_id', 'visited_date']);
            $table->index(['ip_address', 'visited_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
