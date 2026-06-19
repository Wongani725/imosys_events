<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Member;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_participant_login_page()
    {
        $response = $this->get(route('participant.login'));
        $response->assertStatus(200);
    }

    public function test_guest_can_view_forgot_password_page()
    {
        $response = $this->get(route('member.forgot.password.form'));
        $response->assertStatus(200);
    }

    public function test_guest_can_view_register_page()
    {
        $response = $this->get(route('register.form'));
        $response->assertStatus(200);
    }

    public function test_member_can_access_dashboard_when_authenticated()
    {
        $member = Member::factory()->create();
        Event::factory()->create();

        $response = $this->actingAs($member, 'member')
            ->get(route('member-dashboard'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_member_dashboard()
    {
        $response = $this->get(route('member-dashboard'));
        $response->assertStatus(302);
    }
}
