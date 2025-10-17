<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('reservation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); 
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('reservation_id')
                ->references('id')
                ->on('reservations')
                ->onDelete('cascade');

            $table->index(['reservation_id', 'user_id']); 
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('reservation_logs');
    }
};
