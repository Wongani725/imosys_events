<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_template_returns_binary_response()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.members.template'));

        $response->assertStatus(200);
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
    }
}
