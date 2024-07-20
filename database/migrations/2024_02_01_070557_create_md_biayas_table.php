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
        Schema::create('md_biayas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_satuan')->nullable();
            $table->integer('id_maps')->nullable(); 
            $table->string('name_biaya')->nullable();
            $table->string('harga')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_biayas');
    }
};
