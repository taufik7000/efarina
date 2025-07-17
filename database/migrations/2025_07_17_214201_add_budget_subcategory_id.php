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
     Schema::table('pengajuan_anggarans', function (Blueprint $table) {
    $table->unsignedBigInteger('budget_subcategory_id')->nullable()->after('project_id');
    $table->foreign('budget_subcategory_id')->references('id')->on('budget_subcategories')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
