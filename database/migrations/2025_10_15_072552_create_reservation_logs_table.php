<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('reservation_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('action', 100); // tambahkan panjang maksimum
            $table->text('description')->nullable();

            $table->timestamps();

            // Relasi ke tabel reservations
            $table->foreign('reservation_id')
                ->references('id')
                ->on('reservations')
                ->onDelete('cascade');

            // Relasi opsional ke tabel users
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete(); // biar aman kalau user dihapus

            // Index untuk performa query
            $table->index(['reservation_id', 'user_id']);
        });
    }

    /**
     * Hapus tabel saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_logs');
    }
};
