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
        Schema::table('sites', function (Blueprint $table) {
            // Change bigInteger to decimal to support decimal values for more accurate tracking
            $table->decimal('storage_used_mb', 15, 4)->default(0)->change();
            $table->decimal('traffic_used_mb', 15, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Revert back to bigInteger
            $table->bigInteger('storage_used_mb')->default(0)->change();
            $table->bigInteger('traffic_used_mb')->default(0)->change();
        });
    }
};

