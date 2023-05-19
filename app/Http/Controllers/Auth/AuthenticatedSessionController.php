<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use F9Web\ApiResponseHelpers;

/**
 * @group Auth APIs
 */
class AuthenticatedSessionController extends Controller
{
    use ApiResponseHelpers;
    /**
     * Login the user.
     *
     * @bodyParam email string required The user's email or phone number
     * @bodyParam password string required The user's password
     *
     * @response 200
     * @responseParam data The logged in user data
     * @responseParam token The authenticated user token
     */
    public function store(LoginRequest $request)
    {
        if (!$request->wantsJson()) {
            $request->authenticate();

            $request->session()->regenerate();

            return response()->noContent();
        }

        if ($request->has('phone_number')) {
            if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password], true)) {
                return response()->json(['phone_number' => 'The provided credentials are invalid'], 422);
            }
        } elseif ($request->has('email')) {
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
                return response()->json(['email' => 'The provided credentials are invalid'], 422);
            }
        }

        $user = User::when($request->has('email'), function ($query) use ($request) {
                        $query->where('email', $request->email);
                    })
                    ->when($request->has('phone_number'), function ($query) use ($request) {
                        $query->where('phone_number', $request->phone_number);
                    })
                    ->first();

        if ($request->has('device_token') && $request->device_token != '') {
            $user->update([
                'device_token' => $request->device_token,
            ]);
        }

        $token = $user->createToken($request->email)->plainTextToken;

        if ($user->hasRole('admin')) {
            $permissions = $user->getPermissionsViaRoles()->pluck('name');
            $role = $user->getRoleNames()[0];
            return $this->respondWithSuccess(['user' => $user->only('id', 'name', 'email'), 'token' => $token, 'permissions' => $permissions, 'role' => $role]);
        }
        return $this->respondWithSuccess(['data' => $user->only('id', 'name', 'email', 'avatar'), 'token' => $token]);
    }

    /**
     * Logout.
     * @authenticated
     *
     * @response 200
     * @responseParam message Logged out
     */
    public function destroy(Request $request)
    {
        if (!$request->wantsJson()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();
            return redirect('/');
        }

        $request->user()->tokens()->delete();

        return $this->respondOk('Logged out');
    }
}
