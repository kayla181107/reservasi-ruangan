<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // bikin permission (cek dulu, kalau belum ada baru buat)
        Permission::firstOrCreate(
            ['name' => 'manage reservations'],
            ['guard_name' => 'web']
        );
        Permission::firstOrCreate(
            ['name' => 'view rooms'],
            ['guard_name' => 'web']
        );

        // role admin
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );
        $adminRole->givePermissionTo(['manage reservations', 'view rooms']);

        // role karyawan
        $karyawanRole = Role::firstOrCreate(
            ['name' => 'karyawan'],
            ['guard_name' => 'web']
        );
        $karyawanRole->givePermissionTo(['view rooms']);
    }
}
