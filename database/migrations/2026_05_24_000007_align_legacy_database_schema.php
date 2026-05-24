<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignAdminUsers();
        $this->alignStockLevels();
        $this->alignOrders();
        $this->alignSettings();
    }

    public function down(): void
    {
        // Irreversible alignment migration.
    }

    private function alignAdminUsers(): void
    {
        if (! Schema::hasTable('admin_users')) {
            return;
        }

        if (Schema::hasColumn('admin_users', 'password') && ! Schema::hasColumn('admin_users', 'password_hash')) {
            DB::statement('ALTER TABLE admin_users CHANGE password password_hash VARCHAR(255) NOT NULL');
        }

        if (! Schema::hasColumn('admin_users', 'display_name')) {
            DB::statement('ALTER TABLE admin_users ADD display_name VARCHAR(255) NULL AFTER password_hash');
            DB::statement('UPDATE admin_users SET display_name = COALESCE(NULLIF(email, ""), username) WHERE display_name IS NULL OR display_name = ""');
            DB::statement('ALTER TABLE admin_users MODIFY display_name VARCHAR(255) NOT NULL');
        }

        // Normalize rows where email holds the login address but username does not.
        DB::statement('UPDATE admin_users SET username = email WHERE email IS NOT NULL AND email != "" AND username != email');
    }

    private function alignStockLevels(): void
    {
        if (! Schema::hasTable('stock_levels')) {
            return;
        }

        if (Schema::hasColumn('stock_levels', 'product_code')) {
            return;
        }

        Schema::drop('stock_levels');

        Schema::create('stock_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_code', 100);
            $table->string('product_name');
            $table->integer('stock_limit')->nullable();
            $table->unique('product_code');
        });
    }

    private function alignOrders(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('orders', 'payment_reference')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_reference')->nullable()->after('status');
            });
        }
    }

    private function alignSettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (Schema::hasColumn('settings', 'key') && ! Schema::hasColumn('settings', 'id')) {
            DB::statement('ALTER TABLE settings MODIFY `key` VARCHAR(191) NOT NULL');
        }
    }
};
