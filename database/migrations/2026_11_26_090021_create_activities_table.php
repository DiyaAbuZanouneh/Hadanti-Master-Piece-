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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_request_id')->nullable()->constrained('ai_requests')->nullOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('title', 200);
            $table->unsignedSmallInteger('duration');
            $table->string('skill', 100)->nullable();
            $table->string('place', 100)->nullable();
            $table->longText('materials')->nullable();
            $table->longText('steps');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->enum('status', ['planned', 'done', 'skipped'])->default('planned');
            $table->enum('rating', ['liked', 'neutral', 'disliked'])->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};