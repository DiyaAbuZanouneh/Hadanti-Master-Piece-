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
        Schema::create('child_report_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->nullable()->constrained('class_daily_reports')->nullOnDelete();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('date');
            $table->enum('type', ['breakfast', 'lunch', 'nap', 'mood', 'health', 'other']);
            $table->string('value', 50)->nullable();
            $table->text('text')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_report_exceptions');
    }
};