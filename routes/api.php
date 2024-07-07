<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganisationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth.jwt')->group(function () {
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/organisations', [OrganisationController::class, 'index']);
    Route::get('/organisations/{orgId}', [OrganisationController::class, 'show'])->name('organisations.show');
    Route::post('/organisations', [OrganisationController::class, 'store']);
    Route::post('/organisations/{orgId}/users', [OrganisationController::class, 'addUser']);
});

