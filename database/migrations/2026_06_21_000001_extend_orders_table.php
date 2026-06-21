<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'custom_order_id')) {
                $table->string('custom_order_id', 20)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('orders', 'client_payment_reference')) {
                $table->string('client_payment_reference', 255)->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('orders', 'payment_verification_status')) {
                $table->enum('payment_verification_status', ['unverified', 'pending', 'verified', 'rejected'])
                    ->default('unverified')
                    ->after('client_payment_reference');
            }
            if (!Schema::hasColumn('orders', 'payment_verified_at')) {
                $table->dateTime('payment_verified_at')->nullable()->after('payment_verification_status');
            }
            if (!Schema::hasColumn('orders', 'payment_verified_by')) {
                $table->string('payment_verified_by', 100)->nullable()->after('payment_verified_at');
            }
        });

        if (!Schema::hasIndex('orders', 'orders_custom_order_id_unique')) {
            DB::statement('CREATE UNIQUE INDEX orders_custom_order_id_unique ON orders (custom_order_id)');
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_custom_order_id_unique');
            $table->dropColumn([
                'custom_order_id',
                'client_payment_reference',
                'payment_verification_status',
                'payment_verified_at',
                'payment_verified_by',
            ]);
        });
    }
};