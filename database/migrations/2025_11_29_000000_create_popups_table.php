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
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'html'])->default('image');
            $table->string('image_path')->nullable();
            $table->text('html_code')->nullable();
            $table->string('link')->nullable();
            $table->boolean('open_new_window')->default(false);
            $table->enum('display_type', ['overlay', 'list'])->default('overlay'); // 겹치기, 나열하기
            $table->enum('position', ['center', 'top-left', 'top-right', 'bottom-left', 'bottom-right'])->default('center');
            $table->string('target_type')->default('all'); // 'all', 'main', 'attendance', 'point-exchange', 'event-application', 'board_{id}'
            $table->string('target_id')->nullable(); // For board_id or page slug
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};

