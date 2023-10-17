<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\School;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use DatabaseMigrations;

    public function test_get_all_members_success(): void
    {
        $schools = School::factory()->count(4)->create();

        $members = Member::factory()->count(5)->create();

        $members->each(function($member) use ($schools){
            $member->schools()->attach($schools->random());
        });

        $response = $this->json('GET', 'api/members');

        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 5, function(AssertableJson $json) {
                        $json->hasAll(['id', 'name', 'email', 'schools'])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'email' => 'string',  
                            ])
                            ->has('schools', 1, function (AssertableJson $json) {    
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string',
                                    ]);
                                
                            });
                            
                    });
            });
    }
}
