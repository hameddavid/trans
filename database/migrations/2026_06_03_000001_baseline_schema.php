<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline schema for all application-managed tables.
 * Uses hasTable() guards so it is safe to run on existing production databases.
 * Uses hasTable() guards so it is safe to run on existing production databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->id();
                $table->string('surname');
                $table->string('firstname');
                $table->string('othername')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('200');
                $table->string('account_status')->default('active');
                $table->string('title')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('applicants')) {
            Schema::create('applicants', function (Blueprint $table) {
                $table->id();
                $table->string('surname');
                $table->string('firstname');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('mobile')->unique();
                $table->string('matric_number')->nullable()->index();
                $table->string('sex')->nullable();
                $table->string('type')->default('applicant');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('token')->nullable();
                $table->string('picture')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('official_applications')) {
            Schema::create('official_applications', function (Blueprint $table) {
                $table->id('application_id');
                $table->string('matric_number')->index();
                $table->unsignedBigInteger('applicant_id')->index();
                $table->string('delivery_mode')->nullable();
                $table->string('transcript_type')->nullable();
                $table->text('address')->nullable();
                $table->string('email')->nullable();
                $table->string('destination')->nullable();
                $table->string('institutional_username')->nullable();
                $table->string('institutional_password')->nullable();
                $table->string('recipient')->nullable();
                $table->string('app_status')->default('PENDING');
                $table->string('used_token')->nullable()->index();
                $table->string('graduation_year')->nullable();
                $table->string('grad_status')->nullable();
                $table->string('reference')->nullable();
                $table->string('certificate')->nullable();
                $table->string('first_session_in_sch')->nullable();
                $table->string('last_session_in_sch')->nullable();
                $table->string('years_spent')->nullable();
                $table->string('qualification')->nullable();
                $table->string('prog_name')->nullable();
                $table->string('dept')->nullable();
                $table->string('fac')->nullable();
                $table->string('cgpa')->nullable();
                $table->string('class_of_degree')->nullable();
                $table->longText('transcript_raw')->nullable();
                $table->string('recommended_by')->nullable();
                $table->timestamp('recommended_at')->nullable();
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->json('form_fields')->nullable();
                $table->string('edit_token')->nullable();
                $table->string('complaint_sent_by')->nullable();
                $table->timestamp('complaint_sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_applications')) {
            Schema::create('student_applications', function (Blueprint $table) {
                $table->id();
                $table->string('matric_number')->index();
                $table->unsignedBigInteger('applicant_id')->index();
                $table->string('delivery_mode')->nullable();
                $table->string('transcript_type')->nullable();
                $table->text('address')->nullable();
                $table->string('destination')->nullable();
                $table->string('recipient')->nullable();
                $table->string('app_status')->default('PENDING');
                $table->string('graduation_year')->nullable();
                $table->string('grad_status')->nullable();
                $table->string('certificate')->nullable();
                $table->string('first_session_in_sch')->nullable();
                $table->string('last_session_in_sch')->nullable();
                $table->string('years_spent')->nullable();
                $table->string('qualification')->nullable();
                $table->string('prog_name')->nullable();
                $table->string('dept')->nullable();
                $table->string('fac')->nullable();
                $table->string('cgpa')->nullable();
                $table->string('class_of_degree')->nullable();
                $table->longText('transcript_raw')->nullable();
                $table->string('recommended_by')->nullable();
                $table->timestamp('recommended_at')->nullable();
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('admin_applications')) {
            Schema::create('admin_applications', function (Blueprint $table) {
                $table->id();
                $table->string('matric_number')->index();
                $table->unsignedBigInteger('admin_id')->index();
                $table->string('delivery_mode')->nullable();
                $table->string('transcript_type')->nullable();
                $table->text('address')->nullable();
                $table->string('destination')->nullable();
                $table->string('recipient')->nullable();
                $table->string('app_status')->default('PENDING');
                $table->string('graduation_year')->nullable();
                $table->string('grad_status')->nullable();
                $table->string('certificate')->nullable();
                $table->string('first_session_in_sch')->nullable();
                $table->string('last_session_in_sch')->nullable();
                $table->string('years_spent')->nullable();
                $table->string('qualification')->nullable();
                $table->string('prog_name')->nullable();
                $table->string('dept')->nullable();
                $table->string('fac')->nullable();
                $table->string('cgpa')->nullable();
                $table->string('class_of_degree')->nullable();
                $table->longText('transcript_raw')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_transaction')) {
            Schema::create('payment_transaction', function (Blueprint $table) {
                $table->id();
                $table->string('matric_number')->nullable()->index();
                $table->string('email')->nullable();
                $table->string('names')->nullable();
                $table->decimal('amount', 12, 2)->nullable();
                $table->string('rrr')->nullable()->index();
                $table->string('trans_ref')->nullable();
                $table->string('destination')->nullable();
                $table->string('gateway')->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('status_code')->nullable();
                $table->string('status_msg')->nullable();
                $table->string('time_stamp')->nullable();
                $table->string('p_gateway_transaction_id')->nullable();
                $table->unsignedBigInteger('app_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('degree_verification_payment_transaction')) {
            Schema::create('degree_verification_payment_transaction', function (Blueprint $table) {
                $table->id();
                $table->string('matric_number')->nullable();
                $table->string('email')->nullable();
                $table->string('names')->nullable();
                $table->decimal('amount', 12, 2)->nullable();
                $table->string('rrr')->nullable()->index();
                $table->string('trans_ref')->nullable();
                $table->string('destination')->nullable();
                $table->string('gateway')->nullable();
                $table->string('institution_email')->nullable();
                $table->string('institution_name')->nullable();
                $table->string('status_code')->nullable();
                $table->string('status_msg')->nullable();
                $table->string('time_stamp')->nullable();
                $table->string('p_gateway_transaction_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('degree_verification')) {
            Schema::create('degree_verification', function (Blueprint $table) {
                $table->id();
                $table->string('surname')->nullable();
                $table->string('firstname')->nullable();
                $table->string('othername')->nullable();
                $table->string('program')->nullable();
                $table->string('grad_year')->nullable();
                $table->string('institution_email')->nullable();
                $table->string('institution_name')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('request_type')->nullable();
                $table->string('matno_found')->nullable();
                $table->string('status')->default('PENDING');
                $table->string('used_token')->nullable()->index();
                $table->string('yr_of_adms')->nullable();
                $table->string('qualification')->nullable();
                $table->string('dept')->nullable();
                $table->string('fac')->nullable();
                $table->string('treated_by')->nullable();
                $table->timestamp('treated_at')->nullable();
                $table->string('recommended_by')->nullable();
                $table->timestamp('recommended_at')->nullable();
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('forgot_matno')) {
            Schema::create('forgot_matno', function (Blueprint $table) {
                $table->id();
                $table->string('surname')->nullable();
                $table->string('firstname')->nullable();
                $table->string('othername')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('program')->nullable();
                $table->string('date_left')->nullable();
                $table->string('matno_found')->nullable();
                $table->string('status')->default('PENDING');
                $table->string('treated_by')->nullable();
                $table->timestamp('treated_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('applicant_id')->index();
                $table->string('matric_number');
                $table->string('subject');
                $table->text('message');
                $table->string('status')->default('PENDING');
                $table->text('admin_response')->nullable();
                $table->string('responded_by')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->integer('semester');
                $table->char('session', 9);
                $table->string('status', 10)->default('');
                $table->timestamp('created_at')->useCurrent();
                $table->string('updated_at', 191)->nullable();

                $table->index('semester');
                $table->index('session');
            });
        }

        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        // External/read-only tables — exist in production, created here for fresh/test environments only
        if (!Schema::hasTable('t_colleges')) {
            Schema::create('t_colleges', function (Blueprint $table) {
                $table->string('college_id')->primary();
                $table->string('college');
            });
        }

        if (!Schema::hasTable('t_departments')) {
            Schema::create('t_departments', function (Blueprint $table) {
                $table->string('department_id')->primary();
                $table->string('department');
                $table->string('college_id_FK')->index();
            });
        }

        if (!Schema::hasTable('t_programmes')) {
            Schema::create('t_programmes', function (Blueprint $table) {
                $table->string('programme_id')->primary();
                $table->string('programme');
                $table->string('department_id_FK')->index();
            });
        }

        if (!Schema::hasTable('t_college_dept')) {
            Schema::create('t_college_dept', function (Blueprint $table) {
                $table->id();
                $table->string('prog_code')->index();
                $table->string('programme');
                $table->string('department');
                $table->string('college');
            });
        }

        if (!Schema::hasTable('t_student_test')) {
            Schema::create('t_student_test', function (Blueprint $table) {
                $table->increments('ID');
                $table->string('matric_number', 25)->unique();
                $table->string('SURNAME', 35)->nullable();
                $table->string('FIRSTNAME', 35)->nullable();
                $table->string('sex', 5)->nullable()->default('');
                $table->string('EMAIL1', 255)->nullable();
                $table->string('prog_code', 10)->nullable()->index();
                $table->string('status', 15)->nullable();
                $table->string('session_admitted', 45);
                $table->string('session_graduated', 45)->nullable();
            });
        }

        if (!Schema::hasTable('t_course')) {
            Schema::create('t_course', function (Blueprint $table) {
                $table->id();
                $table->string('course_code', 45)->index();
                $table->text('course_title')->nullable();
                $table->integer('unit')->nullable();
                $table->integer('unit_id')->nullable();
            });
        }

        if (!Schema::hasTable('registrations')) {
            Schema::create('registrations', function (Blueprint $table) {
                $table->id();
                $table->string('matric_number', 20)->index();
                $table->integer('semester');
                $table->char('session_id', 9);
                $table->string('course_code', 45);
                $table->string('lecturer_id', 30)->nullable();
                $table->char('status', 1);
                $table->decimal('score', 5, 2)->default(-1);
                $table->decimal('ca', 5, 2)->default(-1);
                $table->integer('total_score')->default(0);
                $table->char('grade', 1);
                $table->string('remarks', 50);
                $table->char('deleted', 1)->default('N');
                $table->char('unit_id', 8)->default('');
                $table->boolean('flag_waver')->default(false);
                $table->timestamp('created_at')->useCurrent();
                $table->string('updated_at', 191)->nullable();
            });
        }

        if (!Schema::hasTable('ug_course_with_pass_mark')) {
            Schema::create('ug_course_with_pass_mark', function (Blueprint $table) {
                $table->id();
                $table->string('course_code')->nullable();
                $table->string('pass_mark')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Reverse order to respect foreign key dependencies
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('forgot_matno');
        Schema::dropIfExists('degree_verification');
        Schema::dropIfExists('degree_verification_payment_transaction');
        Schema::dropIfExists('payment_transaction');
        Schema::dropIfExists('admin_applications');
        Schema::dropIfExists('student_applications');
        Schema::dropIfExists('official_applications');
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('admin');
    }
};
