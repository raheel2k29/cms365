<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\BusinessEntity;
use App\Models\QuoteType;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $salesperson = Role::create(['name' => 'salesperson']);

        // Create permissions
        $permissions = [
            'view quotes', 'create quotes', 'edit quotes', 'delete quotes',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view vendors', 'create vendors', 'edit vendors', 'delete vendors',
            'view reports', 'export data',
            'manage settings', 'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Admin gets all permissions
        $admin->givePermissionTo(Permission::all());

        // Salesperson gets basic permissions
        $salesperson->givePermissionTo([
            'view quotes', 'create quotes', 'edit quotes',
            'view customers', 'create customers', 'edit customers',
            'view vendors',
            'view reports',
        ]);

        // Create admin user
        $adminUser = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@quotecrm.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole('admin');

        // Create salesperson user
        $salesUser = User::create([
            'name'     => 'Sales User',
            'email'    => 'sales@quotecrm.com',
            'password' => Hash::make('password'),
        ]);
        $salesUser->assignRole('salesperson');

        // Business entities
        BusinessEntity::create(['name' => 'ESC', 'code' => 'ESC', 'is_active' => true]);

        // Quote types
        QuoteType::create(['name' => 'Specification', 'code' => 'SPEC', 'is_active' => true]);
        QuoteType::create(['name' => 'Value Engineering', 'code' => 'VE', 'is_active' => true]);
    }
}
