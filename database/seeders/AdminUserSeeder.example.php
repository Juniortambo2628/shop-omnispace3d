<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TEMPLATE. The real seeder holds real people's details, so it is kept out of git:
 *
 *   cp database/seeders/AdminUserSeeder.example.php database/seeders/AdminUserSeeder.php
 *
 * then put the real accounts in the copy. Careful: running it removes every
 * admin account that is not in its list.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Replace these with the real admin accounts. Keep the real copy out of git.
        $users = [
            [
                'username' => 'admin@example.com',
                'email' => 'admin@example.com',
                'display_name' => 'Site Admin',
                'password' => env('SEED_ADMIN_PASSWORD', 'ChangeMe#123'),
                'role' => 'super_admin',
            ],
            [
                'username' => 'editor@example.com',
                'email' => 'editor@example.com',
                'display_name' => 'Product Editor',
                'password' => env('SEED_ADMIN_PASSWORD', 'ChangeMe#123'),
                'role' => 'product_editor',
            ],
        ];

        $usesPasswordHash = Schema::hasColumn('admin_users', 'password_hash');
        $usesEmail = Schema::hasColumn('admin_users', 'email');
        $usesDisplayName = Schema::hasColumn('admin_users', 'display_name');
        $seededEmails = array_column($users, 'email');

        foreach ($users as $user) {
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $row = [
                'username' => $user['username'],
                'role' => $user['role'],
                'active' => 1,
            ];

            if ($usesPasswordHash) {
                $row['password_hash'] = $hash;
            } else {
                $row['password'] = $hash;
            }

            if ($usesEmail) {
                $row['email'] = $user['email'];
            }

            if ($usesDisplayName) {
                $row['display_name'] = $user['display_name'];
            }

            if (Schema::hasColumn('admin_users', 'updated_at')) {
                $row['updated_at'] = now();
            }

            if (! Schema::hasColumn('admin_users', 'created_at')) {
                unset($row['created_at']);
            } else {
                $row['created_at'] = now();
            }

            $match = ['username' => $user['username']];
            if ($usesEmail) {
                $existing = DB::table('admin_users')
                    ->where('email', $user['email'])
                    ->orWhere('username', $user['username'])
                    ->first();

                if ($existing) {
                    $match = ['id' => $existing->id];
                }
            }

            DB::table('admin_users')->updateOrInsert($match, $row);
        }

        DB::table('admin_users')
            ->where(function ($query) use ($seededEmails) {
                $query->whereNotIn('username', $seededEmails);

                if (Schema::hasColumn('admin_users', 'email')) {
                    $query->where(function ($inner) use ($seededEmails) {
                        $inner->whereNull('email')->orWhereNotIn('email', $seededEmails);
                    });
                }
            })
            ->delete();
    }
}
