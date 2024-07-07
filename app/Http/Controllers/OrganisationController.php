<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Str;
use App\Helpers\ResponseHelper;

class OrganisationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $organisations = $user->organisations->map(function ($organisation) {
            return [
                'orgId' => $organisation->orgId,
                'name' => $organisation->name,
                'description' => $organisation->description,
            ];
        });

        return ResponseHelper::success('Organisations retrieved successfully', [
            'organisations' => $organisations,
        ]);
    }

    public function show($orgId)
    {
        try {
            $user = auth()->user();
            $organisation = Organisation::where('orgId', $orgId)
                ->whereHas('users', function($query) use ($user) {
                    $query->where('organisation_user.userId', $user->userId);
                })->firstOrFail();

            $formattedOrganisation = [
                'orgId' => $organisation->orgId,
                'name' => $organisation->name,
                'description' => $organisation->description,
            ];

            return ResponseHelper::success('Organisation retrieved successfully', $formattedOrganisation);
        } catch (\Exception $e) {
            return ResponseHelper::error('Resource not found', 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string'
            ]);

            $organisation = Organisation::create([
                'orgId' => (string) Str::uuid(),
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);

            $organisation->users()->attach(auth()->id());

            return ResponseHelper::success('Organisation created successfully', [
                'orgId' => $organisation->orgId,
                'name' => $organisation->name,
                'description' => $organisation->description,
            ], 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Client error', 400);
        }
    }

    public function addUser(Request $request, $orgId)
    {
        try {
            $validated = $request->validate([
                'userId' => 'required|uuid|exists:users,userId'
            ]);

            $organisation = Organisation::where('orgId', $orgId)
                ->whereHas('users', function($query) {
                    $query->where('organisation_user.userId', auth()->id());
                })->firstOrFail();

            $user = User::findOrFail($validated['userId']);
            $organisation->users()->attach($user);

            return ResponseHelper::success('User added to organisation successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Client error', 400);
        }
    }
}
