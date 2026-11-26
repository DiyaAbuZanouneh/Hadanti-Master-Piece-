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
        Schema::create('class_daily_reports', function (Blueprint $table) {

            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('date');
            $table->enum('breakfast', ['all', 'some', 'none'])->nullable();
            $table->enum('lunch', ['all', 'some', 'none'])->nullable();
            $table->time('nap_from')->nullable();
            $table->time('nap_to')->nullable();
            $table->enum('mood', ['happy', 'calm', 'tired', 'fussy'])->nullable();
            $table->text('activities_text')->nullable();
            $table->text('general_note')->nullable();
            $table->timestamps();

            $table->unique(['classroom_id', 'date']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_daily_reports');
    }
};
