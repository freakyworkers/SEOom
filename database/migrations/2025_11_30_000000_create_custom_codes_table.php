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
        Schema::create('custom_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('location'); // head, head_css, head_js, first_page_top, first_page_bottom, content_top, content_bottom, sidebar_top, sidebar_bottom, body
            $table->text('code')->nullable();
            $table->timestamps();
            
            $table->unique(['site_id', 'location']);
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_codes');
    }
};





