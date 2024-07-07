<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganisationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCannotSeeOtherOrganisations()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $user1->organisations()->attach($organisation);

        $response = $this->actingAs($user2)->getJson(route('organisations.show', $organisation->orgId));

        $response->assertStatus(404);
    }
}
