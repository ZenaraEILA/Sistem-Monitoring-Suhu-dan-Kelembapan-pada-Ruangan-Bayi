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
        Schema::create('archived_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->date('archive_date');
            $table->integer('record_count');
            $table->json('summary'); // {avg_temp, max_temp, min_temp, avg_humidity, incidents_count}
            $table->longText('data'); // Compressed JSON of all records
            $table->timestamp('archived_at');
            $table->timestamps();
            $table->unique(['device_id', 'archive_date']);
            $table->index('archive_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_data');
    }
};
