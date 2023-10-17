<?php

namespace Tests\Feature;

use App\Models\School;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    use DatabaseMigrations;

    public function test_get_all_schools_success(): void
    {
        School::factory()->count(5)->create();

        $response = $this->getJson('/api/schools');

        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {
                $json->hasall(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 5, function (AssertableJson $json) {
                        $json->hasall(['id', 'name'])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                            ]);
                    });
            });
    }
}
