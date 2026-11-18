<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('companies')->updateOrInsert(
            ['name' => 'Example Company'],
            ['updated_at' => $now, 'created_at' => $now],
        );

        $email = env('SUPERADMIN_EMAIL', 'superadmin@example.com');
        if (DB::table('users')->where('email', $email)->exists()) {
            return;
        }

        DB::statement(
            'INSERT INTO users (name, email, password, role, company_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, NULL, ?, ?)',
            [
                env('SUPERADMIN_NAME', 'Super Administrator'),
                $email,
                Hash::make(env('SUPERADMIN_PASSWORD', 'ChangeMe123!')),
                'SuperAdmin',
                $now,
                $now,
            ],
        );
    }
}
