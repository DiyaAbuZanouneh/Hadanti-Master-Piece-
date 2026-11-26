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
        Schema::create('teacher_certificates', function (Blueprint $table) {

            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('issuer', 150)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file', 255);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_certificates');
    }
};
