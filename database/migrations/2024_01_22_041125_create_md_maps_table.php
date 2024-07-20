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
        Schema::create('md_maps', function (Blueprint $table) {
            $table->id();
            $table->string('id_perusahaan')->nullable();
            $table->string('name')->nullable();
            $table->mediumText('notes')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->date('date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_maps');
    }
};
