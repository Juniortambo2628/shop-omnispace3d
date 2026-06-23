<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasIndex('orders', 'orders_email_index')) {
                $table->index('email');
            }
            if (!Schema::hasIndex('orders', 'orders_event_slug_custom_order_id_index')) {
                $table->index(['event_slug', 'custom_order_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['event_slug', 'custom_order_id']);
        });
    }
};
