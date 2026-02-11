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
        Schema::create('device_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->timestamp('last_data_at')->nullable();
            $table->enum('status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->integer('offline_minutes')->default(0);
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();
            $table->unique('device_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_statuses');
    }
};
