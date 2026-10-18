<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Category-based fees. A school defines fee categories (Tuition, Transport,
 * Exam, Admission, ...). Each category is given a per-class amount via
 * class_fee_plans. Monthly categories generate a recurring invoice per month;
 * one_time categories generate once; exam categories are linked to an exam.
 *
 * Per-student overrides let an admin raise/lower a specific student's amount
 * for a category (scholarship, sibling discount, penalty, etc.).
 *
 * Generated dues are written into fee_invoices (extended below) so they reuse
 * the existing payment_transactions online/offline flow unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 120);
            // monthly = recurring each month; one_time = single due; exam = tied to an exam
            $table->enum('type', ['monthly', 'one_time', 'exam'])->default('monthly');
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'is_active'], 'idx_fee_cat_school');
        });

        Schema::create('class_fee_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('fee_category_id');
            $table->unsignedBigInteger('class_id');
            $table->decimal('amount', 10, 2);
            // For monthly categories: day of month the invoice is due (1-28).
            $table->unsignedTinyInteger('due_day')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'fee_category_id', 'class_id'], 'uniq_class_fee_plan');
            $table->index(['school_id', 'class_id'], 'idx_class_fee_plan_class');
            $table->foreign('fee_category_id')->references('id')->on('fee_categories')->onDelete('cascade');
        });

        Schema::create('student_fee_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_category_id');
            // The effective amount for this student for this category. When set,
            // it replaces the class plan amount at invoice-generation time.
            $table->decimal('amount', 10, 2);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'fee_category_id'], 'uniq_student_fee_override');
            $table->foreign('fee_category_id')->references('id')->on('fee_categories')->onDelete('cascade');
        });

        // Extend fee_invoices so category/monthly/exam dues coexist with the
        // legacy structure-based invoices. All nullable → no impact on old rows.
        Schema::table('fee_invoices', function (Blueprint $table) {
            // Legacy columns were NOT NULL (structure-based invoices). Category
            // invoices don't have a structure/installment, so relax them.
            $table->unsignedBigInteger('fee_structure_id')->nullable()->change();
            $table->unsignedBigInteger('fee_installment_id')->nullable()->change();

            $table->unsignedBigInteger('fee_category_id')->nullable()->after('fee_structure_id');
            $table->unsignedBigInteger('exam_id')->nullable()->after('fee_category_id');
            $table->unsignedSmallInteger('period_year')->nullable()->after('exam_id');
            $table->unsignedTinyInteger('period_month')->nullable()->after('period_year');
            $table->string('title', 150)->nullable()->after('invoice_number');
            $table->index(['school_id', 'fee_category_id'], 'idx_invoice_category');
            $table->index(['school_id', 'period_year', 'period_month'], 'idx_invoice_period');
        });
    }

    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoice_category');
            $table->dropIndex('idx_invoice_period');
            $table->dropColumn(['fee_category_id', 'exam_id', 'period_year', 'period_month', 'title']);
        });
        Schema::dropIfExists('student_fee_overrides');
        Schema::dropIfExists('class_fee_plans');
        Schema::dropIfExists('fee_categories');
    }
};
