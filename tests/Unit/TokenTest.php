<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function testTokenGeneration(): void
    {
        $this->assertNotEmpty($this->token);
        $this->assertTrue(is_string($this->token));
    }

    public function testTokenExpiration(): void
{
    $payload = JWTAuth::setToken($this->token)->getPayload();

    $expTime = Carbon::createFromTimestamp($payload['exp']);
    $expectedExpTime = now()->addMinutes(config('jwt.ttl'));

    $this->assertTrue($expTime->diffInSeconds($expectedExpTime) <= 2);
    $this->assertTrue($expTime->greaterThan(now()));
}

    public function testUserDetailsInToken(): void
    {
        $payload = JWTAuth::setToken($this->token)->getPayload();

        $this->assertEquals($this->user->getJWTIdentifier(), $payload['sub']);
        $this->assertEquals($this->user->email, $payload['email']);
        $this->assertEquals($this->user->userId, $payload['userId']);
        $this->assertEquals($this->user->firstName, $payload['firstName']);
        $this->assertEquals($this->user->lastName, $payload['lastName']);
    }
}
