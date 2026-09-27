<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'locale' => $data['locale'] ?? 'id',
        ]);

        event(new Registered($user));

        return response()->json([
            'data' => new UserResource($user),
            'token' => $user->createToken($data['device_name'], ['user'])->plainTextToken,
        ], 201);
    }

    public function login(LoginRequest $request, AuditLogService $audit): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->where('email', mb_strtolower($data['email']))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => [trans('auth.failed')]]);
        }

        $audit->record($user, 'auth.api_login', $user, request: $request);

        return response()->json([
            'data' => new UserResource($user),
            'token' => $user->createToken($data['device_name'], [$user->role->value])->plainTextToken,
        ]);
    }

    public function logout(Request $request, AuditLogService $audit): JsonResponse
    {
        $audit->record($request->user(), 'auth.api_logout', $request->user(), request: $request);
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => __('healo.signed_out')]);
    }
}
