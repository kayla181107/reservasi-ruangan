<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('day_of_week')->after('date')->nullable();

            // Simpan jam dalam format string "HH:ii" (contoh: 09:00)
            $table->string('start_time', 5)->change();
            $table->string('end_time', 5)->change();
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('day_of_week');

            $table->time('start_time')->change();
            $table->time('end_time')->change();
        });
    }
};
