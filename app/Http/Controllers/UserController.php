<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    
    public function show($userId)
    {
        $user = User::with('organisations')->findOrFail($userId);
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => [
                'userId' => $user->userId,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ], 200);
    }
}
