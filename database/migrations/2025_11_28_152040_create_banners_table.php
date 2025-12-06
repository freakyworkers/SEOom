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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('location'); // header, main_top, main_bottom, content_top, content_bottom, sidebar_top, sidebar_bottom, left_margin, right_margin
            $table->string('image_path');
            $table->string('link')->nullable();
            $table->boolean('open_new_window')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['site_id', 'location']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
