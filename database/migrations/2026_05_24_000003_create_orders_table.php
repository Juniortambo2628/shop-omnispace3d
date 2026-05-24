<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('event_slug', 100);
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->string('booth_number', 50);
            $table->text('special_instructions')->nullable();
            $table->string('payment_method', 50);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status', 50)->default('Pending');
            $table->string('payment_reference')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->index('event_slug');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
