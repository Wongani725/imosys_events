<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_redirects_guest_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(302);
    }

    public function test_admin_events_page_redirects_guest()
    {
        $response = $this->get(route('events'));
        $response->assertStatus(302);
    }

    public function test_admin_members_page_redirects_guest()
    {
        $response = $this->get(route('admin.members.index'));
        $response->assertStatus(302);
    }

    public function test_admin_can_view_member_create_page_redirects_guest()
    {
        $response = $this->get(route('admin.members.create'));
        $response->assertStatus(302);
    }

    public function test_admin_bookings_page_redirects_guest()
    {
        $response = $this->get(route('get-bookers'));
        $response->assertStatus(302);
    }

    public function test_critical_route_names_exist()
    {
        $routes = [
            'dashboard',
            'events',
            'participant.login',
            'member-dashboard',
            'admin.members.index',
            'admin.members.create',
            'admin.members.import.form',
            'admin.bulk-booking.index',
            'admin.notifications.index',
            'admin.reports.index',
            'admin.master-meal-tags.index',
            'admin.walkin.create',
            'member.forgot.password.form',
            'member.profile.setup.form',
            'view_participants',
            'web.cancel.booking',
            'web.restore.booking',
        ];

        foreach ($routes as $routeName) {
            try {
                $uri = route($routeName);
                $this->assertNotEmpty($uri, "Route {$routeName} generated empty URI");
            } catch (\Exception $e) {
                $this->fail("Route {$routeName} failed: " . $e->getMessage());
            }
        }
    }
}
