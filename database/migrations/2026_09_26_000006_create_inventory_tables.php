<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory / stock management: categories -> items (with stock quantity) and
 * stock transactions (purchase in / issue out) that adjust item quantity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->timestamp('created_at')->useCurrent();

            $table->index('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name', 150);
            $table->string('sku', 50)->nullable();
            $table->string('unit', 20)->default('pcs'); // pcs, kg, litre, box
            $table->integer('quantity')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('location', 100)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'category_id'], 'idx_school_category');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('inventory_categories')->onDelete('set null');
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('item_id');
            $table->enum('type', ['in', 'out']); // stock in (purchase), stock out (issue)
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('reference', 150)->nullable(); // supplier / issued-to / bill no
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'item_id'], 'idx_school_item');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('inventory_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
    }
};
