<?php

namespace App\Http\Controllers;

use App\Models\User;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

/**
 * User APIs
 * @authenticated
 *
 * @group User APIs
 */
class UserController extends Controller
{
    use ApiResponseHelpers;

    public function __construct()
    {
        $this->middleware(['auth', 'auth:sanctum']);
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Get user details.
     * @authenticated
     *
     * @response 200
     * @responseParam data The user details
     */
    public function show()
    {
        $role = auth()->user()->getRoleNames()[0];

        if ($role === 'vendor') {
            $user = auth()->user()->load('services');
        } else {
            $user = auth()->user();
        }

        return $this->respondWithSuccess(['data' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
