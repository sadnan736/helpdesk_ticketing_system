<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard(); // lets this seeder set role and timestamps

        // On Render, set SEED_PASSWORD so the public demo isn't using 'password'
        $password = Hash::make(env('SEED_PASSWORD', 'password'));

        // firstOrCreate: safe to run on every deploy, never overwrites changed passwords
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User', 'password' => $password, 'role' => 'super_admin', 'email_verified_at' => now(),
        ]);
        $agent = User::firstOrCreate(['email' => 'agent@example.com'], [
            'name' => 'Agent Amy', 'password' => $password, 'role' => 'agent', 'email_verified_at' => now(),
        ]);
        $customer = User::firstOrCreate(['email' => 'customer@example.com'], [
            'name' => 'Customer Chris', 'password' => $password, 'role' => 'customer', 'email_verified_at' => now(),
        ]);

        if (Ticket::count() === 0) {
            $tickets = [
                // Already past its deadline: will be escalated on the first sla:check
                [
                    'subject' => 'Payroll export fails for March',
                    'description' => 'The monthly payroll export stops at 80 percent and shows a timeout error.',
                    'category' => 'HRM', 'priority' => 'high', 'agent_id' => $agent->id,
                    'created_at' => now()->subHours(3), 'due_at' => now()->subHour(),
                ],
                // 83% of its time used: will trigger a warning
                [
                    'subject' => 'Patient portal login errors',
                    'description' => 'Several patients report a blank page after entering their credentials.',
                    'category' => 'Healthcare', 'priority' => 'critical', 'agent_id' => $agent->id,
                    'created_at' => now()->subMinutes(100), 'due_at' => now()->addMinutes(20),
                ],
                // Unassigned: shows the admin assignment flow
                [
                    'subject' => 'Leave balance report request',
                    'description' => 'Please add a report showing remaining leave days per department.',
                    'category' => 'HRM', 'priority' => 'low', 'agent_id' => null,
                    'created_at' => now(), 'due_at' => now()->addDays(3),
                ],
            ];

            foreach ($tickets as $t) {
                Ticket::create($t + ['customer_id' => $customer->id]);
            }
        }

        Model::reguard();
    }
}