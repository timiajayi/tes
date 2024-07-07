<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrganisationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCannotSeeOtherOrganisations()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create an organisation and attach it to user1
        $organisation = Organisation::factory()->create();
        $user1->organisations()->attach($organisation);

        // Generate a token for user2
        $token = JWTAuth::fromUser($user2);

        // Attempt to access the organisation as user2
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('organisations.show', $organisation->orgId));

        // Assert that the request fails with a 404 (Not Found) status
        $response->assertStatus(404);
    }

    public function testUserCanSeeOwnOrganisation()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an organisation and attach it to the user
        $organisation = Organisation::factory()->create();
        $user->organisations()->attach($organisation);

        // Generate a token for the user
        $token = JWTAuth::fromUser($user);

        // Attempt to access the organisation as the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('organisations.show', $organisation->orgId));

        // Assert that the request succeeds
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'orgId',
                'name',
                'description',
            ],
        ]);
    }
}
