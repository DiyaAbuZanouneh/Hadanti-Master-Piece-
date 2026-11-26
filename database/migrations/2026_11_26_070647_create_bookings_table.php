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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('child_id')->constrained('children')->restrictOnDelete();
            $table->foreignId('nursery_id')->constrained('nurseries')->restrictOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->enum('type', ['daily', 'monthly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->set('days_of_week', ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'])->nullable();
            $table->decimal('price', 10, 3);
            $table->decimal('commission_rate', 5, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'paid', 'cancelled', 'completed'])->default('pending');
            $table->text('reject_reason')->nullable();
            $table->foreignId('renewed_from_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
