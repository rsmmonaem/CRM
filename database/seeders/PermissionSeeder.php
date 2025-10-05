<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = ['dashboard', 'leads', 'lead_details', 'services', 'statuses', 'users'];
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                // Special case for dashboard, only 'view'
                if ($module === 'dashboard' && $action !== 'view') {
                    continue;
                }
                Permission::firstOrCreate([
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }
    }
}