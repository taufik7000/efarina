<?php
// Buat migration baru untuk hapus field kode
// php artisan make:migration remove_kode_fields_from_budget_tables

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kode_kategori dari budget_categories
        Schema::table('budget_categories', function (Blueprint $table) {
            $table->dropUnique(['kode_kategori']); // Hapus unique constraint dulu
            $table->dropColumn('kode_kategori');
        });

        // Hapus kode_subkategori dari budget_subcategories  
        Schema::table('budget_subcategories', function (Blueprint $table) {
            $table->dropUnique(['kode_subkategori']); // Hapus unique constraint dulu
            $table->dropColumn('kode_subkategori');
        });
    }

    public function down(): void
    {
        // Jika perlu rollback
        Schema::table('budget_categories', function (Blueprint $table) {
            $table->string('kode_kategori')->unique()->after('nama_kategori');
        });

        Schema::table('budget_subcategories', function (Blueprint $table) {
            $table->string('kode_subkategori')->unique()->after('nama_subkategori');
        });
    }
};