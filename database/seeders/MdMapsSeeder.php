<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MdMapsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::create([
            'name' => 'superadmin',
            'email' => 'superadmin@tako.co.id',
            'password' => bcrypt('12341234'),
        ]);
        $superadmin->assignRole('superadmin');

        $superadmin = User::create([
            'name' => 'Fantoca',
            'email' => 'fantoca@tako.co.id',
            'password' => bcrypt('12341234'),
        ]);
        $superadmin->assignRole('superadmin');

        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@tako.co.id',
            'password' => bcrypt('12341234'),
        ]);
        $admin->assignRole('admin');

        $superuser = User::create([
            'name' => 'superuser',
            'email' => 'superuser@tako.co.id',
            'password' => bcrypt('12341234'),
        ]);
        $superuser->assignRole('superuser');

        $superuser2 = User::create([
            'name' => 'superuser2',
            'email' => 'superuser2@tako.co.id',
            'password' => bcrypt('12341234'),
        ]);
        $superuser2->assignRole('superuser2');
    }
}
