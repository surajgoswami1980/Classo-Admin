<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->string('name', 100);
            $table->unsignedBigInteger('class_id');
            $table->decimal('total_amount', 10, 2);
            $table->integer('installment_count')->default(1);
            $table->decimal('late_fee_per_day', 8, 2)->default(0);
            $table->decimal('late_fee_max', 10, 2)->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'academic_session_id', 'class_id'], 'idx_school_session_class');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('fee_heads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->string('name', 100);
            $table->decimal('amount', 10, 2);
            $table->tinyInteger('is_optional')->default(0);
            $table->enum('head_type', ['tuition', 'transport', 'library', 'lab', 'sports', 'exam', 'other']);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'fee_structure_id'], 'idx_fee_structure');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
        });

        Schema::create('fee_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->integer('installment_number');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'fee_structure_id'], 'idx_school_structure');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('cascade');
        });

        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedBigInteger('fee_installment_id');
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial'])->default('pending');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'student_id'], 'idx_school_student');
            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->index(['school_id', 'due_date'], 'idx_due_date');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('fee_invoice_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('parent_user_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['upi', 'credit_card', 'debit_card', 'net_banking', 'cash', 'cheque']);
            $table->enum('gateway', ['razorpay', 'offline'])->default('razorpay');
            $table->string('razorpay_order_id', 100)->nullable();
            $table->string('razorpay_payment_id', 100)->nullable();
            $table->string('razorpay_signature', 255)->nullable();
            $table->enum('status', ['initiated', 'success', 'failed', 'refunded'])->default('initiated');
            $table->text('failure_reason')->nullable();
            $table->string('receipt_url', 500)->nullable();
            $table->decimal('platform_commission', 10, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->index('razorpay_order_id', 'idx_razorpay_order');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_installments');
        Schema::dropIfExists('fee_heads');
        Schema::dropIfExists('fee_structures');
    }
};
