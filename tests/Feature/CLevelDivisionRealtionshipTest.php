<?php

namespace Tests\Feature;

use App\Models\CLevel;
use App\Models\Division;
use App\Models\CLevelDivisionRelationship;
use Tests\TestCase;

class CLevelDivisionControllerTest extends TestCase
{

    /** @test */
    public function it_can_create_a_c_level_and_division()
    {
        $response = $this->postJson('/api/v1/c-level-division', [
            'c_level' => 'cmo',
            'division' => 'Sales',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                 ]);
    }

    /** @test */
    public function it_can_show_a_c_level_division()
    {

        $response = $this->getJson('/api/v1/c-level-division/4');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'mesage',
                     'payload' => [
                         [
                             'c_level_id' ,
                             'division_id' ,
                             'created_at',
                             'updated_at' ,
                         ]
                     ],
                 ]);
                }
    /** @test */
    public function it_can_update_a_c_level_division()
    {
        // Update the relationship
        $response = $this->putJson("/api/v1/c-level-division/4", [
            'c_level' => 'cto',
            'division' => 'front end',
        ]);
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'mesage',
                     'payload' => [
                         'c_level_id',
                         'division_id',
                         'created_at',
                         'updated_at'
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_when_showing_nonexistent_division()
    {
        $response = $this->getJson('/api/v1/c-level-division/999');

        $response->assertStatus(404)
                 ->assertJsonStructure(['message']);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_relationship()
    {
        $response = $this->putJson('/api/v1/c-level-division/999', [
            'c_level_id' => 2,
        ]);

        $response->assertStatus(404)
                 ->assertJsonStructure(['message']);
    }

    /** @test */
    public function it_can_delete_a_c_level_division()
    {
        $response = $this->deleteJson('/api/v1/c-level-division/4/4');
        $response->assertStatus(200)
                 ->assertJsonStructure(['message']);
    }
}
