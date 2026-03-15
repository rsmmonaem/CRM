<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;

class CallStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        $statuses = [
            'Connected',
            'No Answer',
            'Busy',
            'Switch Off',
            'Not Reachable',
            'Wrong Number',
            'Disconnected',
            'Interested',
            'Not Interested',
            'Requested Callback',
        ];

        foreach ($statuses as $statusName) {
            Status::updateOrCreate(
                ['name' => $statusName, 'type' => 'call'],
                ['created_by' => $adminId]
            );
        }

        // Remove the 'Connectedssss' if it exists
        Status::where('name', 'Connectedssss')->where('type', 'call')->delete();
    }
}
