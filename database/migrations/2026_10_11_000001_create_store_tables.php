<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School Store: schools list items for sale (books, uniforms, stationery,
 * anything). Students browse in the app Store tab, pay the item price online,
 * and an invoice/order is generated. There is NO delivery — the order is
 * "purchased" (paid, not yet collected). When the student collects the item
 * from the school counter, the admin marks the order "completed".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 80)->nullable(); // books, uniform, stationery, etc.
            $table->string('image', 500)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->nullable();        // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active'], 'idx_store_item_active');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('store_item_id');
            $table->unsignedBigInteger('user_id');      // who purchased (student/parent login)
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('order_number', 60)->unique();
            $table->string('item_name', 200);           // snapshot
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            // purchased = paid, awaiting collection; completed = collected
            $table->enum('status', ['pending_payment', 'purchased', 'completed', 'cancelled'])->default('pending_payment');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('gateway', 30)->nullable();
            $table->string('razorpay_order_id', 100)->nullable();
            $table->string('razorpay_payment_id', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status'], 'idx_store_order_status');
            $table->index(['user_id'], 'idx_store_order_user');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('store_item_id')->references('id')->on('store_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_orders');
        Schema::dropIfExists('store_items');
    }
};
