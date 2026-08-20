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
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->string('parent_signature_name')->nullable()->after('authorized_fetcher_relation');
            $table->date('date_of_first_attendance')->nullable()->after('parent_signature_name');
            $table->boolean('doc_submitted_form138')->default(false)->after('date_of_first_attendance');
            $table->boolean('doc_submitted_psa_birth')->default(false)->after('doc_submitted_form138');
            $table->boolean('doc_submitted_form137')->default(false)->after('doc_submitted_psa_birth');
            $table->boolean('doc_submitted_baptismal')->default(false)->after('doc_submitted_form137');
            $table->boolean('doc_submitted_pic_1x1')->default(false)->after('doc_submitted_baptismal');
            $table->boolean('doc_submitted_pic_2x2')->default(false)->after('doc_submitted_pic_1x1');
            $table->boolean('doc_submitted_itr')->default(false)->after('doc_submitted_pic_2x2');
            $table->boolean('doc_submitted_unemployment')->default(false)->after('doc_submitted_itr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropColumn([
                'parent_signature_name',
                'date_of_first_attendance',
                'doc_submitted_form138',
                'doc_submitted_psa_birth',
                'doc_submitted_form137',
                'doc_submitted_baptismal',
                'doc_submitted_pic_1x1',
                'doc_submitted_pic_2x2',
                'doc_submitted_itr',
                'doc_submitted_unemployment',
            ]);
        });
    }
};
