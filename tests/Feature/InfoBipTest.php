<?php

namespace Tests\Feature;

use App\Services\InfoBipTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class InfoBipTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testSendMessage(){
        $infobipService = new InfoBipTestService();
        $response = $infobipService->sendMessage('+221781114327', 'Test message from Laravel');

        $this->assertJson($response);
        $this->assertStringContainsString('messageCount', $response);
    }
}
