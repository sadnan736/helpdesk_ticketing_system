<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TicketAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeTicket(User $customer, array $overrides = []): Ticket
    {
        return Ticket::create($overrides + [
            'subject' => 'Test ticket',
            'description' => 'Something is broken',
            'category' => 'HRM',
            'priority' => 'low',
            'customer_id' => $customer->id,
            'due_at' => now()->addDay(),
        ]);
    }

    public function test_customer_cannot_view_another_customers_ticket(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = $this->makeTicket($owner);

        $this->actingAs($stranger)->get(route('tickets.show', $ticket))->assertForbidden();
        $this->actingAs($owner)->get(route('tickets.show', $ticket))->assertOk();
    }

    public function test_agent_can_only_open_assigned_tickets(): void
    {
        $customer = User::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $assigned = $this->makeTicket($customer, ['agent_id' => $agent->id]);
        $other = $this->makeTicket($customer);

        $this->actingAs($agent)->get(route('tickets.show', $assigned))->assertOk();
        $this->actingAs($agent)->get(route('tickets.show', $other))->assertForbidden();
    }

    public function test_only_admins_can_open_the_user_admin_area(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($customer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_sla_command_escalates_breached_tickets_to_admins(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $ticket = $this->makeTicket(User::factory()->create(), ['due_at' => now()->subMinute()]);

        $this->artisan('sla:check')->assertSuccessful();

        $this->assertEquals(2, $ticket->fresh()->escalation_level);
        Notification::assertSentTo($admin, SlaAlert::class);
    }
}