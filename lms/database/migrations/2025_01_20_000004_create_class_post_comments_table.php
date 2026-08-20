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
        Schema::create('class_post_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_post_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->string('file_path')->nullable(); // For attached files in comments
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // For nested comments
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('class_post_id')->references('id')->on('class_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('class_post_comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_post_comments');
    }
};
