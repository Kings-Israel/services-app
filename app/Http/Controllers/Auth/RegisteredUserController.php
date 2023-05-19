<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\NumberGenerator;
use App\Http\Controllers\Controller;
use App\Jobs\SendSMS;
use App\Models\User;
use F9Web\ApiResponseHelpers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

/**
 * @group Auth APIs
 */
class RegisteredUserController extends Controller
{
    use ApiResponseHelpers;

    /**
     * Register the user
     *
     * @bodyParam first_name string required The vendor's first name
     * @bodyParam last_name string required The vendor's last name
     * @bodyParam email string required The vendor's email
     * @bodyParam phone_number string required The vendor's phone number
     * @bodyParam password string required The vendor's password
     * @bodyParam role string required The person's role(vendor or user)
     *
     * @response 200
     * @responseParam data The registered user's data
     * @responseParam token The registered user's token
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'string']
        ]);

        // CHeck if role is either user or vendor
        if (strtolower($request->role) !== 'user' && strtolower($request->role) !== 'vendor') {
            return response()->json(['message' => 'Invalid role, please select user or vendor'], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        strtolower($request->role) === 'user' ? $user->assignRole('user') : $user->assignRole('vendor');

        if ($request->has('device_token') && $request->device_token != '' && $request->device_token != null) {
            $user->update([
                'device_token' => $request->device_token,
            ]);
        }

        Auth::login($user);

        $verification_code = NumberGenerator::generateUniqueNumber(User::class, 'phone_verification_code');

        $user->update([
            'phone_verification_code' => $verification_code,
        ]);

        SendSMS::dispatchAfterResponse($user->phone_number, 'Your phone verification code is '.$user->phone_verification_code);

        if ($request->wantsJson()) {

            $token = $user->createToken($request->email)->plainTextToken;

            return $this->respondWithSuccess(['data' => $user->only('id', 'name', 'email', 'avatar'), 'token' => $token]);
        }

        return response()->noContent();
    }

    /**
     * Validate the OTP
     *
     * @bodyParam code string required The Entered code
     *
     * @response 200
     * @responseParam message The code was validated successfully
     */
    public function validateOtp(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string'
        ]);

        $user = User::where('phone_verification_code', $request->code)->first();

        if (!$user) {
            if($request->wantsJson()) {
                return response()->json(['message' => 'The entered code was invalid'], 400);
            }
            return back()->withErrors('Invalid code entered');
        }

        $user->update([
            'phone_verification_code' => NULL,
            'phone_number_verified_at' => now(),
        ]);

        // return $request->wantsJson() ? response()->json(['message' => 'Phone verified successfully'], 200) : redirect()->route('')->with('suucess', 'Phone number verified successfully');
        return $this->respondWithSuccess('Phone number verified successfully');
    }

    /**
     * Request password reset code
     *
     * @bodyParam phone_number string required The user's phone number
     *
     * @response 200
     * @responseParam message Verification code sent successfully
     */
    public function sendResetPasswordCode(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['message' => 'The phone number is invalid'], 422);
        }

        $user->update([
            'phone_verification_code' => NumberGenerator::generateUniqueNumber(User::class, 'phone_verification_code'),
        ]);

        SendSMS::dispatchAfterResponse($request->phone_number, 'Your verification code is '.$user->phone_verification_code);

        return $this->respondWithSuccess('Verification code sent successfully');
    }

    /**
     * Verify Code
     * Used in registration and reset password requests
     *
     * @bodyParam code string required The verification code
     *
     * @response 200
     * @responseParam message The verification was successful
     */
    public function verifyOtp(Request $request)
    {
        $this->validate($request, [
            'code' => 'required'
        ]);

        $user = User::where('phone_verification_code', $request->code)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $user->update([
            'phone_verification_code' => NULL,
            'phone_number_verified_at' => now()
        ]);

        return $this->responseWithSuccess('Phone Number verified successfully');
    }

    /**
     * Reset Password
     *
     * @bodyParam password string required New password
     * @bodyParam password_confirmation string required New password confirmation
     *
     * @response 200
     * @responseParam message The password reset was successful
     * @responseParam data The user's data
     * @responseParam token The user's token
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'code' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('phone_verification_code', $request->code)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification code'], 422);
        }

        $user->update([
            'phone_verification_code' => NULL,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);

        $user->createToken($user->email)->plainTextToken;

        $token = $user->createToken($request->email)->plainTextToken;

        return $this->respondWithSuccess(['message' => 'Password reset successfully' ,'data' => $user->only('id', 'name', 'email', 'avatar'), 'token' => $token]);
    }
}
