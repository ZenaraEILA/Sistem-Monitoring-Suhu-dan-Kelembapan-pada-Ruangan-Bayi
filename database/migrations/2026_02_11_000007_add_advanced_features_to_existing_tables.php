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
        Schema::table('monitorings', function (Blueprint $table) {
            $table->timestamp('unsafe_detected_at')->nullable()->after('is_emergency'); // Waktu kondisi tidak aman terdeteksi
            $table->timestamp('action_taken_at')->nullable()->after('action_note'); // Waktu tindakan dilakukan
            $table->integer('response_time_minutes')->nullable()->after('action_taken_at'); // Selisih waktu respons
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->enum('stability_status', ['stable', 'unstable', 'unknown'])->default('unknown')->after('location'); // Status stabilitas ruangan
            $table->integer('stability_score')->default(100)->after('stability_status'); // Skor 0-100
            $table->json('early_warning_patterns')->nullable()->after('stability_score'); // Pola peringatan dini
            $table->timestamp('last_stability_check')->nullable()->after('early_warning_patterns');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropColumn(['unsafe_detected_at', 'action_taken_at', 'response_time_minutes']);
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['stability_status', 'stability_score', 'early_warning_patterns', 'last_stability_check']);
        });
    }
};
