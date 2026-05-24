<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'juniortambo2628@gmail.com',
                'email' => 'juniortambo2628@gmail.com',
                'display_name' => 'Kevin Tambo',
                'password' => 'Admin2#10',
                'role' => 'super_admin',
            ],
            [
                'username' => 'susan.mboya@geminvest.co.ke',
                'email' => 'susan.mboya@geminvest.co.ke',
                'display_name' => 'Susan Mboya',
                'password' => 'Silversky#10',
                'role' => 'super_admin',
            ],
            [
                'username' => 'claire.asena@geminvest.co.ke',
                'email' => 'claire.asena@geminvest.co.ke',
                'display_name' => 'Claire Asena',
                'password' => 'Geminvest#10',
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
