<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Store orders now carry a customer-facing invoice number, generated the
 * moment payment is verified (order flips to "purchased"). The invoice PDF
 * itself is rendered on demand by the API; only the number is persisted so
 * it is stable and referenceable from the admin and the app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->string('invoice_number', 60)->nullable()->after('order_number');
            $table->timestamp('invoiced_at')->nullable()->after('paid_at');
            $table->index(['school_id', 'invoice_number'], 'idx_store_order_invoice');
        });
    }

    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropIndex('idx_store_order_invoice');
            $table->dropColumn(['invoice_number', 'invoiced_at']);
        });
    }
};
