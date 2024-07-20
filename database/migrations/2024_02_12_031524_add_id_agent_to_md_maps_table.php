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
        Schema::table('md_maps', function (Blueprint $table) {
            $table->string('id_agent')->nullable()->after('id_perusahaan'); // Menambahkan kolom baru
            $table->string('name_penerima')->nullable()->after('name'); // Menambahkan kolom baru
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('md_maps', function (Blueprint $table) {
            $table->dropColumn('id_agent'); // Menghapus kolom jika Anda melakukan rollback
            $table->dropColumn('name_penerima'); // Menghapus kolom jika Anda melakukan rollback
        });
    }
};
