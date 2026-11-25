<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('nursery_id')->constrained('nurseries')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->unique()->constrained('classrooms')->nullOnDelete();
            $table->string('specialization', 150)->nullable();
            $table->string('qualification', 150)->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->text('bio')->nullable();
            $table->string('cv_file', 255)->nullable();
            $table->date('hire_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};