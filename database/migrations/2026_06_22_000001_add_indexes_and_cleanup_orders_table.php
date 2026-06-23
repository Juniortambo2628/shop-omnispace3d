<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('email');
            $table->index(['event_slug', 'custom_order_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('category')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['event_slug', 'custom_order_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('category')->nullable()->change();
        });
    }
};
