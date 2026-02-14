<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Membuat tabel users dengan kolom role berbasis enum
     * Role default: 'petugas'
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // ✅ User information
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // 🔐 Role Management
            // - Default: 'petugas'
            // - Hanya admin yang bisa diubah oleh admin
            $table->enum('role', ['admin', 'petugas'])
                ->default('petugas')
                ->index()
                ->comment('Role user: admin atau petugas (default: petugas)');
            
            // ✅ User Status & Tracking
            $table->boolean('is_active')
                ->default(true)
                ->index()
                ->comment('Status aktif/nonaktif user');
            
            $table->timestamp('last_login_at')
                ->nullable()
                ->comment('Tracking kapan user last login');
            
            $table->rememberToken();
            $table->timestamps();

            // 📝 Indexes untuk performa query
            $table->index('email');
            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
