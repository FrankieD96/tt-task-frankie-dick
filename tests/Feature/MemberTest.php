<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\School;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Assert;
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

        $response = $this->getJson('/api/members');

        $response->assertOk()

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

    public function test_member_add_invalid_data(): void
    {
        $response = $this->postJson('/api/members', [
            'name' => 3,
            'email' => 'test',
            'school_ids' => [1],
            'school_ids.*' => 'test'
        ]);

        $response->assertStatus(422)
            ->assertInvalid(['name', 'email', 'school_ids.0']);   
    }

    public function test_member_add_success(): void
    {
       $school = School::factory()->create();
       
       $response = $this->postJson('/api/members', [
        'name' => 'John Doe',
        'email' => 'john.doe@email.com',
        'school_ids' => [$school->id],
       ]);

       $response->assertStatus(201);
       $response->assertJson(function (AssertableJson $json) {
        $json->has('message')
            ->where(
                'message',
                "Member added"
            );
       });

       $this->assertDatabaseHas('members', [
        'name' => 'John Doe',
        'email' => 'john.doe@email.com',
       ]);

       $this->assertDatabaseHas('member_school', [
        'member_id' => 1,
        'school_id' => $school->id
       ]);

    }

    public function test_get_all_members_by_school_filter_success(): void
    {
        $schools = School::factory()->count(2)->create();

        $members = Member::factory()->count(10)->create();

        $members->each(function($member) use ($schools) {
            $member->schools()->attach($schools->first());
        });

        $filterBySchool = $schools->first();

        $response = $this->getJson("/api/members?school={$filterBySchool->id}");

        $response->assertOk()
            ->assertJson(function (AssertableJson $json) use ($filterBySchool) {
                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 10, function(AssertableJson $json) use ($filterBySchool) {
                        $json->hasAll(['id', 'name', 'email'])
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'email' => 'string',  
                            ])
                            ->has('schools', 1, function (AssertableJson $json) use ($filterBySchool) {    
                                $json->where('id', $filterBySchool->id)
                                    ->where('name', $filterBySchool->name);
                            });
                    });
        });
    }

    public function test_get_all_members_by_school_invalid_school(): void
    {
        $response = $this->getJson('/api/members?school=1000');
        $response->assertStatus(422);
        $response->assertInvalid('school');
    }
}
