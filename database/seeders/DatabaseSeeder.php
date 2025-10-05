<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Status;
use App\Models\Lead;
use App\Models\LeadDetail;
use App\Models\Permission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed permissions first
        $this->call(PermissionSeeder::class);
        
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create regular user
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        // Create services
        $services = [
            Service::create(['name' => 'Web Development', 'created_by' => $admin->id]),
            Service::create(['name' => 'Mobile App Development', 'created_by' => $admin->id]),
            Service::create(['name' => 'Digital Marketing', 'created_by' => $admin->id]),
            Service::create(['name' => 'Consulting', 'created_by' => $admin->id]),
        ];

        // Create statuses
        $statuses = [
            Status::create(['name' => 'New', 'created_by' => $admin->id]),
            Status::create(['name' => 'Contacted', 'created_by' => $admin->id]),
            Status::create(['name' => 'Qualified', 'created_by' => $admin->id]),
            Status::create(['name' => 'Proposal Sent', 'created_by' => $admin->id]),
            Status::create(['name' => 'Negotiation', 'created_by' => $admin->id]),
            Status::create(['name' => 'Closed Won', 'created_by' => $admin->id]),
            Status::create(['name' => 'Closed Lost', 'created_by' => $admin->id]),
        ];

        // Create sample leads
        $leads = [
            Lead::create([
                'name' => 'John Doe',
                'company_name' => 'Tech Corp',
                'phone' => '+1234567890',
                'email' => 'john@techcorp.com',
                'service_id' => $services[0]->id,
                'status_id' => $statuses[0]->id,
                'assigned_user_id' => $user->id,
                'created_by' => $admin->id,
            ]),
            Lead::create([
                'name' => 'Jane Smith',
                'company_name' => 'Marketing Inc',
                'phone' => '+1234567891',
                'email' => 'jane@marketinginc.com',
                'service_id' => $services[2]->id,
                'status_id' => $statuses[1]->id,
                'assigned_user_id' => $user->id,
                'created_by' => $admin->id,
            ]),
            Lead::create([
                'name' => 'Bob Johnson',
                'company_name' => 'StartupXYZ',
                'phone' => '+1234567892',
                'email' => 'bob@startupxyz.com',
                'service_id' => $services[1]->id,
                'status_id' => $statuses[2]->id,
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id,
            ]),
        ];

        // Create sample lead details (calls)
        foreach ($leads as $lead) {
            // Create a call for today
            LeadDetail::create([
                'lead_id' => $lead->id,
                'call_followup_date' => today()->subDays(1),
                'call_followup_summary' => 'Initial contact made. Client showed interest in our services.',
                'next_call_date' => today(), // This will show up in today's calls
                'created_by' => $lead->assigned_user_id,
            ]);

            // Create a pending call (overdue)
            LeadDetail::create([
                'lead_id' => $lead->id,
                'call_followup_date' => today()->subDays(2),
                'call_followup_summary' => 'Follow-up call scheduled but missed.',
                'next_call_date' => today()->subDays(1), // This will show up in pending calls
                'created_by' => $lead->assigned_user_id,
            ]);

            // Create a future call
            LeadDetail::create([
                'lead_id' => $lead->id,
                'call_followup_date' => today(),
                'call_followup_summary' => 'Follow-up call scheduled for next week.',
                'next_call_date' => today()->addDays(7),
                'created_by' => $lead->assigned_user_id,
            ]);
        }
    }
}
