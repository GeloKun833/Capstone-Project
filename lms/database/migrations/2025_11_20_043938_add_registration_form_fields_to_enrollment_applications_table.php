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
            // Student Information - Additional Fields
            $table->date('date_enrolled')->nullable()->after('grade_level_applying_for');
            $table->string('lrn')->nullable()->after('date_enrolled');
            $table->string('esc_no')->nullable()->after('lrn');
            
            // Address breakdown
            $table->string('address_lot_block_village')->nullable()->after('address');
            $table->string('address_barangay_district')->nullable()->after('address_lot_block_village');
            $table->string('address_city_municipality')->nullable()->after('address_barangay_district');
            
            // Age (calculated, but stored for reference)
            $table->integer('age_years')->nullable()->after('date_of_birth');
            $table->integer('age_months')->nullable()->after('age_years');
            
            // COVID-19 Vaccination
            $table->enum('covid_vaccinated', ['Yes', 'No'])->nullable()->after('age_months');
            $table->date('covid_first_shot_date')->nullable()->after('covid_vaccinated');
            $table->date('covid_full_vaccination_date')->nullable()->after('covid_first_shot_date');
            
            // Additional personal information
            $table->string('religion')->nullable()->after('covid_full_vaccination_date');
            $table->string('citizenship')->nullable()->after('religion');
            $table->string('birthplace')->nullable()->after('citizenship');
            
            // Previous school details
            $table->string('previous_school_id')->nullable()->after('previous_school');
            $table->string('previous_school_location')->nullable()->after('previous_school_id');
            $table->enum('previous_school_type', ['Public', 'Private'])->nullable()->after('previous_school_location');
            $table->string('psa_birth_cert_no')->nullable()->after('previous_school_type');
            
            // Father's Information
            $table->string('father_last_name')->nullable()->after('parent_relationship');
            $table->string('father_first_name')->nullable()->after('father_last_name');
            $table->string('father_middle_name')->nullable()->after('father_first_name');
            $table->enum('father_education', ['Elementary graduate', 'High School graduate', 'College graduate', 'Vocational', 'Masters Doctorate degree', 'Did not attend school', 'Others'])->nullable()->after('father_middle_name');
            $table->enum('father_employment', ['Full time', 'Part time', 'Self-employed', 'Unemployed'])->nullable()->after('father_education');
            $table->string('father_company_name')->nullable()->after('father_employment');
            $table->string('father_work_address')->nullable()->after('father_company_name');
            $table->string('father_contact_no')->nullable()->after('father_work_address');
            $table->string('father_email')->nullable()->after('father_contact_no');
            
            // Mother's Information
            $table->string('mother_last_name')->nullable()->after('father_email');
            $table->string('mother_first_name')->nullable()->after('mother_last_name');
            $table->string('mother_middle_name')->nullable()->after('mother_first_name');
            $table->enum('mother_education', ['Elementary graduate', 'High School graduate', 'College graduate', 'Vocational', 'Masters Doctorate degree', 'Did not attend school', 'Others'])->nullable()->after('mother_middle_name');
            $table->enum('mother_employment', ['Full time', 'Part time', 'Self-employed', 'Unemployed'])->nullable()->after('mother_education');
            $table->string('mother_company_name')->nullable()->after('mother_employment');
            $table->string('mother_work_address')->nullable()->after('mother_company_name');
            $table->string('mother_contact_no')->nullable()->after('mother_work_address');
            $table->string('mother_email')->nullable()->after('mother_contact_no');
            
            // Family Income Information
            $table->enum('family_income_bracket', ['Below 10,000.00', '10,001-30,000', 'Above 30,000.00'])->nullable()->after('mother_email');
            $table->integer('no_of_siblings')->nullable()->after('family_income_bracket');
            $table->integer('no_of_siblings_studying')->nullable()->after('no_of_siblings');
            $table->text('siblings_schools')->nullable()->after('no_of_siblings_studying');
            
            // Guardian/Authorized Fetcher Information
            $table->string('guardian_name')->nullable()->after('emergency_contact_phone');
            $table->string('guardian_relation')->nullable()->after('guardian_name');
            $table->string('guardian_contact_no')->nullable()->after('guardian_relation');
            $table->string('guardian_email')->nullable()->after('guardian_contact_no');
            $table->string('authorized_fetcher')->nullable()->after('guardian_email');
            $table->string('authorized_fetcher_relation')->nullable()->after('authorized_fetcher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropColumn([
                'date_enrolled',
                'lrn',
                'esc_no',
                'address_lot_block_village',
                'address_barangay_district',
                'address_city_municipality',
                'age_years',
                'age_months',
                'covid_vaccinated',
                'covid_first_shot_date',
                'covid_full_vaccination_date',
                'religion',
                'citizenship',
                'birthplace',
                'previous_school_id',
                'previous_school_location',
                'previous_school_type',
                'psa_birth_cert_no',
                'father_last_name',
                'father_first_name',
                'father_middle_name',
                'father_education',
                'father_employment',
                'father_company_name',
                'father_work_address',
                'father_contact_no',
                'father_email',
                'mother_last_name',
                'mother_first_name',
                'mother_middle_name',
                'mother_education',
                'mother_employment',
                'mother_company_name',
                'mother_work_address',
                'mother_contact_no',
                'mother_email',
                'family_income_bracket',
                'no_of_siblings',
                'no_of_siblings_studying',
                'siblings_schools',
                'guardian_name',
                'guardian_relation',
                'guardian_contact_no',
                'guardian_email',
                'authorized_fetcher',
                'authorized_fetcher_relation',
            ]);
        });
    }
};
