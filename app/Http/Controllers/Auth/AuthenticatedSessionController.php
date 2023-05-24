<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use F9Web\ApiResponseHelpers;
use Symfony\Component\HttpFoundation\Response;

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
            return $this->respondWithSuccess(['user' => $user->only('id', 'first_name', 'last_name', 'email'), 'token' => $token, 'permissions' => $permissions, 'role' => $role]);
        }
        return $this->respondWithSuccess(['data' => $user->only('id', 'first_name', 'last_name', 'email', 'avatar'), 'token' => $token]);
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

    /**
     *
     * Social Login
     *
     * Login through google
     * @bodyParam name string required The name of the user
     * @bodyParam email string required The email of the user
     * @bodyParam phone_number string The user's phone number
     * @bodyParam token string A google provided token
     * @bodyParam email_verified boolean Whether the user email is verified
     *
     * @response 200
     *
     * @responseField token The authentication token that will be used to make other requests
     * @responseField data The authenticated user information
     *
     */
    public function googleAuthenticate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);

        try{
            $user = User::where('email', $request->email)->first();
            if($user){
                $user->update([
                    'google_id' => $request->has('token') && $request->token != '' ? $request->token : NULL,
                    'device_token' => $request->has('device_token') && $request->device_token != '' ? $request->device_token : NULL,
                ]);

                Auth::login($user);

                $token = $user->createToken($user->email)->plainTextToken;

                return $this->respondWithSuccess(['data' => $user->only('id', 'first_name', 'last_name', 'email', 'avatar'), 'token' => $token]);
            } else {

                $user = User::create([
                    'first_name' => explode(' ', $request->name)[0],
                    'last_name' => explode(' ', $request->name)[1] != '' ? explode(' ', $request->name)[1] : NULL,
                    'phone_number' => $request->has('phone_number') && $request->phone_number != '' ? $request->phone_number : NULL,
                    'email' => $request->email,
                    'email_verified_at' => $request->has('email_verified') && $request->email_verified == 'true' ? now() : NULL,
                    'password' => bcrypt('password'),
                    'google_id' => $request->has('token') && $request->token != '' ? $request->token : NULL,
                    'avatar' => $request->has('photo_url') && $request->photo_url != '' ? $request->photo_url : NULL,
                    'device_token' => $request->has('device_token') && $request->device_token != '' ? $request->device_token : NULL,
                ]);

                $savedUser = User::where('id', $user->id)->first();

                $savedUser->assignRole('user');

                $token = $user->createToken($request->email)->plainTextToken;

                Auth::login($savedUser);

                return $this->respondWithSuccess(['data' => $user->only('id', 'first_name', 'last_name', 'email', 'avatar'), 'token' => $token]);
            }
        }catch(\Exception $exception){
            info($exception);
            return response()->json(['message' => $exception], Response::HTTP_BAD_REQUEST);
        }
    }
}
