<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Tambah kolom day_of_week setelah date
            $table->string('day_of_week')->after('date')->nullable();

            // Pastikan kolom start_time & end_time tipe time
            $table->time('start_time')->change();
            $table->time('end_time')->change();
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Hapus kolom day_of_week
            $table->dropColumn('day_of_week');

            // Balikin ke string (sesuai kondisi awal)
            $table->string('start_time')->change();
            $table->string('end_time')->change();
        });
    }
};
