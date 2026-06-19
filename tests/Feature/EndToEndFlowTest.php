<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_load()
    {
        $guestPages = [
            route('participant.login'),
            route('member.forgot.password.form'),
        ];

        foreach ($guestPages as $page) {
            $response = $this->get($page);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Guest page {$page} returned {$response->status()}"
            );
        }
    }

    public function test_critical_routes_exist()
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
