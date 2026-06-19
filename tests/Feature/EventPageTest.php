<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Member;
use App\Models\EventPrices;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_events_scope_returns_only_active()
    {
        Event::factory()->create(['event_status' => 'active', 'event_id' => 'ACTIVE-1']);
        Event::factory()->create(['event_status' => 'inactive', 'event_id' => 'INACTIVE-1']);

        $activeEvents = Event::active()->get();

        $this->assertCount(1, $activeEvents);
        $this->assertEquals('ACTIVE-1', $activeEvents->first()->event_id);
    }

    public function test_events_ordered_by_start_date()
    {
        Event::factory()->create([
            'event_id' => 'EVT-2',
            'start_date' => '2026-08-01',
        ]);
        Event::factory()->create([
            'event_id' => 'EVT-1',
            'start_date' => '2026-07-01',
        ]);

        $events = Event::latest()->get();
        $this->assertEquals('EVT-2', $events->first()->event_id);
    }

    public function test_event_has_prices_relationship()
    {
        $event = Event::factory()->create();
        EventPrices::factory()->count(2)->create(['event_id' => $event->event_id]);

        $this->assertCount(2, $event->prices);
    }

    public function test_event_has_hotels_relationship()
    {
        $event = Event::factory()->create();
        Hotel::factory()->count(3)->create(['event_id' => $event->event_id]);

        $this->assertCount(3, $event->hotels);
    }

    public function test_member_can_view_event_dashboard()
    {
        $member = Member::factory()->create();
        Event::factory()->create([
            'event_status' => 'active',
        ]);

        $response = $this->actingAs($member, 'member')
            ->get(route('member-dashboard'));

        $response->assertStatus(200);
    }
}
