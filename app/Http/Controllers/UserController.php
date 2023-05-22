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

    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $search_query = $request->query('search_query');

        if ($request->query('per_page')) {
            $users = User::with('services.images', 'services.categories')
                        ->when($search_query && $search_query != '', function($query) use ($search_query) {
                            $query->where('name', 'LIKE', '%'.$search_query.'%')
                                ->orWhere('email', 'LIKE', '%'.$search_query.'%')
                                ->orWhere('phone_number', 'LIKE', '%'.$search_query.'%');
                        })
                        ->paginate($per_page);
        } else {
            $users = User::with('services.images', 'services.categories')->get();
        }

        $users = $users->filter(function ($user) {
            return !$user->hasRole('admin');
        })->values()->all();

        return $this->respondWithSuccess(['data' => $users]);
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
