<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organisation;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

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
