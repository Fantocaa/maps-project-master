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
        Schema::table('md_biayas', function (Blueprint $table) {
            $table->string('id_customer')->nullable()->after('id');
            $table->string('id_jenis_barang')->nullable()->after('id_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_biayas', function (Blueprint $table) {
            $table->dropColumn("id_customer");
            $table->dropColumn("id_jenis_barang");
        });
    }
};
