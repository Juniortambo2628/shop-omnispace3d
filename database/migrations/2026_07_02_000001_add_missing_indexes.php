<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasIndex('orders', 'orders_booth_number_index')) {
                $table->index('booth_number');
            }
            if (!Schema::hasIndex('orders', 'orders_company_name_index')) {
                $table->index('company_name');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasIndex('order_items', 'order_items_product_id_index')) {
                $table->index('product_id');
            }
            if (!Schema::hasIndex('order_items', 'order_items_category_index')) {
                $table->index('category');
            }
        });

        Schema::table('admin_products', function (Blueprint $table) {
            if (!Schema::hasIndex('admin_products', 'admin_products_category_id_index')) {
                $table->index('category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['booth_number']);
            $table->dropIndex(['company_name']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['category']);
        });

        Schema::table('admin_products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
        });
    }
};
