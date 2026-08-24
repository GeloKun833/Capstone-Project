<?php

use App\Support\SchemaForeign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('weight_settings')) {
            return;
        }

        try {
            Schema::create('weight_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('component_id');
                $table->decimal('weight', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('semester_id')->nullable();
                $table->timestamps();

                SchemaForeign::add($table, 'subject_id', 'subjects');
                SchemaForeign::add($table, 'component_id', 'subject_components');
                SchemaForeign::add($table, 'academic_year_id', 'academic_years', 'set null');
                SchemaForeign::add($table, 'semester_id', 'semesters', 'set null');

                $table->unique(['subject_id', 'component_id', 'academic_year_id', 'semester_id'], 'weight_settings_unique');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                return;
            }

            throw $e;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_settings');
    }
};
