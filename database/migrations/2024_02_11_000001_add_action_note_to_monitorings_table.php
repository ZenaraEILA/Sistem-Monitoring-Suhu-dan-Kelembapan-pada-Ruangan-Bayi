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
            $table->text('action_note')->nullable()->after('status')->comment('Catatan tindakan perawat');
            $table->integer('consecutive_unsafe_count')->default(0)->after('action_note')->comment('Hitungan berturut-turut kondisi tidak aman');
            $table->boolean('is_emergency')->default(false)->after('consecutive_unsafe_count')->comment('Flag untuk kondisi darurat (>5 menit tidak normal)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropColumn(['action_note', 'consecutive_unsafe_count', 'is_emergency']);
        });
    }
};
