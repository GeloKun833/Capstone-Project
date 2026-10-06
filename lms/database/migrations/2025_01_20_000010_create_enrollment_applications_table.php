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
        Schema::create('enrollment_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_relationship')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('grade_level_applying_for');
            $table->enum('status', ['draft', 'pending', 'under_review', 'approved', 'rejected', 'needs_documents'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_applications');
    }
};
