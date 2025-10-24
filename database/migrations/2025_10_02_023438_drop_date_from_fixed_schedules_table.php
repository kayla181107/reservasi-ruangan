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
        // Cek dulu apakah kolom 'date' masih ada
        if (Schema::hasColumn('fixed_schedules', 'date')) {
            Schema::table('fixed_schedules', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali kolom 'date' kalau rollback
        Schema::table('fixed_schedules', function (Blueprint $table) {
            $table->date('date')->nullable();
        });
    }
};
