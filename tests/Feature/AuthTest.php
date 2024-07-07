<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Token Tests
    public function testTokenGeneration()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this->assertNotEmpty($token);
        $this->assertTrue(is_string($token));
    }

    public function testTokenExpiration()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $payload = JWTAuth::setToken($token)->getPayload();

        $expTime = Carbon::createFromTimestamp($payload['exp']);
        $expectedExpTime = now()->addMinutes(config('jwt.ttl'));

        $this->assertTrue($expTime->diffInSeconds($expectedExpTime) <= 1);
        $this->assertTrue($expTime->greaterThan(now()));
    }

    public function testUserDetailsInToken()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertEquals($user->getJWTIdentifier(), $payload['sub']);
        $this->assertEquals($user->email, $payload['email']);
        $this->assertEquals($user->userId, $payload['userId']);
        $this->assertEquals($user->firstName, $payload['firstName']);
        $this->assertEquals($user->lastName, $payload['lastName']);
    }

    // Organisation Access Tests
    public function testUserCannotSeeOtherOrganisations()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $user1->organisations()->attach($organisation);

        $token = JWTAuth::fromUser($user2);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('organisations.show', $organisation->orgId));

        $response->assertStatus(404);
    }

    public function testUserCanSeeOwnOrganisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $user->organisations()->attach($organisation);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('organisations.show', $organisation->orgId));

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

    // Auth Controller Tests
    public function testRegisterSuccessfullyWithDefaultOrganisation()
    {
        $response = $this->postJson(route('auth.register'), [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'accessToken',
                    'user' => [
                        'userId',
                        'firstName',
                        'lastName',
                        'email',
                        'phone',
                    ],
                ],
            ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals("John's Organisation", $user->organisations->first()->name);
    }

    public function testLoginSuccessfully()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'accessToken',
                    'user' => [
                        'userId',
                        'firstName',
                        'lastName',
                        'email',
                        'phone',
                    ],
                ],
            ]);
    }

    public function testRegisterFailsIfRequiredFieldsAreMissing()
    {
        $requiredFields = ['firstName', 'lastName', 'email', 'password'];

        foreach ($requiredFields as $field) {
            $data = [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
                'password' => 'password',
            ];
            unset($data[$field]);

            $response = $this->postJson(route('auth.register'), $data);

            $response->assertStatus(422)
                ->assertJsonValidationErrors($field)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'errors' => [
                        $field
                    ]
                ]);
        }
    }

    public function testRegisterFailsIfDuplicateEmail()
    {
        User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        $response = $this->postJson(route('auth.register'), [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'phone' => '1234567890',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }
}
