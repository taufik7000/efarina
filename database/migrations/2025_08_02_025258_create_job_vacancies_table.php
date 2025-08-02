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
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('job_type', ['Full-time', 'Part-time', 'Contract', 'Internship']);
            $table->string('location');
            $table->text('description');
            $table->text('requirements');
            $table->string('salary_range')->nullable();
            $table->date('application_deadline');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};