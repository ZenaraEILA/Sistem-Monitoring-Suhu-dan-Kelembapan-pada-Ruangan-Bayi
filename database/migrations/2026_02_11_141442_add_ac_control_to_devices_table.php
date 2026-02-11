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
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('ac_enabled')->default(false);
            $table->float('ac_set_point')->default(25.0); // Default 25°C
            $table->boolean('ac_status')->default(false); // ON/OFF
            $table->float('ac_min_temp')->default(15.0);  // Minimum AC temp
            $table->float('ac_max_temp')->default(30.0);  // Maximum AC temp
            $table->string('ac_api_url')->nullable();     // ESP8266 API endpoint
            $table->string('ac_api_key')->nullable();     // API key for ESP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'ac_enabled',
                'ac_set_point', 
                'ac_status',
                'ac_min_temp',
                'ac_max_temp',
                'ac_api_url',
                'ac_api_key'
            ]);
        });
    }
};
