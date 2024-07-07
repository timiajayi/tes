<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenTest extends TestCase
{
    public function testTokenGeneration()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertEquals($user->getJWTIdentifier(), $payload['sub']);
        $this->assertTrue($payload->hasKey('exp'));
        $this->assertTrue($payload->get('exp') > now()->timestamp);
    }
}
