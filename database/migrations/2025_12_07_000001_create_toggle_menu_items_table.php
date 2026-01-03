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
        Schema::create('toggle_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toggle_menu_id')->constrained('toggle_menus')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['toggle_menu_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toggle_menu_items');
    }
};





