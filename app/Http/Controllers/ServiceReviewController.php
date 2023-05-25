<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceReview;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

/**
 * Reviews APIs
 *
 * @group Reviews APIs
 */
class ServiceReviewController extends Controller
{
    use ApiResponseHelpers;

    /**
     * Get reviews
     *
     * @response 200
     * @responseParam data A list of reviews
     */
    public function reviews(Request $request)
    {
        $per_page = $request->query('per_page');
        $search = $request->query('q');

        if ($request->query('per_page')) {
            $services_reviews = ServiceReview::with('user', 'service.user')
                                                ->when(auth()->check() && auth()->user()->hasRole('user'), function ($query) {
                                                    $query->whereHas('user', function ($query) {
                                                        $query->where('id', auth()->id());
                                                    });
                                                })
                                                ->when(auth()->check() && auth()->user()->hasRole('vendor'), function ($query) {
                                                    $vendor_services = auth()->user()->services->pluck('id');
                                                    $query->whereIn('service_id', $vendor_services);
                                                })
                                                ->when($search && $search != '', function($query) use ($search) {
                                                    $query->whereHas('service', function($query) use ($search) {
                                                        $query->where('title', 'LIKE', '%'.$search.'%');
                                                    })
                                                    ->where(function($query) use ($search) {
                                                        $query->whereHas('user', function($query) use ($search) {
                                                            $query->where('first_name', 'LIKE', '%'.$search.'%')
                                                                ->orWhere('last_name', 'LIKE', '%'.$search.'%')
                                                                ->orWhere('email', 'LIKE', '%'.$search.'%');
                                                        });
                                                    });
                                                })
                                                ->orderBy('created_at', 'DESC')
                                                ->paginate($per_page);
        } else {
            $services_reviews = ServiceReview::with('user', 'service.user')
                                                ->when(auth()->check() && auth()->user()->hasRole('user'), function ($query) {
                                                    $query->whereHas('user', function ($query) {
                                                        $query->where('id', auth()->id());
                                                    });
                                                })
                                                ->when(auth()->check() && auth()->user()->hasRole('vendor'), function ($query) {
                                                    $vendor_services = auth()->user()->services->pluck('id');
                                                    $query->whereIn('service_id', $vendor_services);
                                                })
                                                ->orderBy('created_at', 'DESC')
                                                ->get();
        }

        return $this->respondWithSuccess(['data' => $services_reviews]);
    }

    /**
     * Get a review
     *
     * @urlParam ID The ID of the review
     *
     * @response 200
     * @responseParam data The review details
     */
    public function review($id)
    {
        $services_review = ServiceReview::with('user', 'service.user')
                                                ->when(auth()->check() && auth()->user()->hasRole('user'), function ($query) {
                                                    $query->whereHas('user', function ($query) {
                                                        $query->where('id', auth()->id());
                                                    });
                                                })
                                                ->when(auth()->check() && auth()->user()->hasRole('vendor'), function ($query) {
                                                    $vendor_services = auth()->user()->services->pluck('id');
                                                    $query->whereIn('service_id', $vendor_services);
                                                })
                                                ->where('id', $id)
                                                ->first();

        return $this->respondWithSuccess(['data' => $services_review]);
    }

    /**
     * Store a review for a service
     *
     * @bodyParam service_id string required The ID of the service
     * @bodyParam rating int required The rating of the service
     * @bodyParam review string The service review
     *
     * @response 201
     *
     * @responseParam message Review created successfully
     * @responseParam data The saved review
     */
    public function store(Request $request)
    {
        // TODO: Add check if the user has contacted the service provider

        $request->validate([
            'service_id' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        $review = auth()->user()->serviceReview()->create([
            'service_id' => $request->service_id,
            'rating' => $request->rating,
            'review' => $request->has('review') && $request->review != '' && $request->review != null ? $request->review : NULL
        ]);

        // Get the total number of ratings
        $reviews_count = ServiceReview::where('service_id', $request->service_id)->whereDate('created_at', '<=', now()->addMonths(6))->count();

        // Get the service average rating
        $service = Service::find($request->service_id);

        //Add the new rating to the average service rating
        $added_rating = $service->average_rating + $request->rating;

        // Divide by the total number of ratings
        $new_rating = $added_rating / $reviews_count;

        $service->update([
            'average_rating' => $new_rating
        ]);

        return $this->respondCreated(['message' => 'Review created successfully', 'data' => $review]);
    }

    /**
     * Update a review
     *
     * @authenticated
     *
     * @urlParam id The id of the review
     * @bodyParam rating The rating of the service
     * @bodyParam rating int required The rating of the service
     * @bodyParam review string The review to of the service
     *
     * @response 200
     *
     * @responseParam message Review updated successfully
     * @responseParam data The updated review
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required',
            'review' => 'nullable|string',
        ]);

        $review = ServiceReview::find($id);

        abort_if($review->user_id != auth()->id(), 401, 'Permission denied.');

        $review->update([
            'rating' => $request->rating,
            'review' => $request->has('review') && $request->review != '' && $request->review != null ? $request->review : $review->review,
        ]);

        // Get the total number of ratings
        $reviews_count = ServiceReview::where('service_id', $review->service_id)->whereDate('created_at', '<=', now()->addMonths(6))->count();

        // Get the service average rating
        $service = Service::find($review->service_id);

        //Add the new rating to the average service rating
        $added_rating = $service->average_rating + $request->rating;

        // Divide by the total number of ratings
        $new_rating = $added_rating / $reviews_count;

        $service->update([
            'average_rating' => $new_rating
        ]);

        return $this->respondWithSuccess(['message' => 'Review updated successfully', 'data' => $review]);
    }

    /**
     * Delete a review
     *
     * @authenticated
     *
     * @urlParam ID The ID of the review to delete
     *
     * @response 200
     *
     * @responseParam message The review was deleted successfully
     * @responseParam data The service which the review belonged to
     */
    public function delete($id)
    {
        $review = ServiceReview::find($id);

        if (!$review) {
            return $this->respondNotFound('The review does not exist');
        }

        abort_if($review->user_id != auth()->id(), 401, 'Permission denied.');

        $service = $review->service;

         // Get the total number of ratings
         $reviews_count = ServiceReview::where('service_id', $review->service_id)->whereDate('created_at', '<=', now()->addMonths(6))->count();

         //Add the new rating to the average service rating
         $added_rating = $service->average_rating - $review->rating;

         // Divide by the total number of ratings
         $new_rating = $added_rating / $reviews_count;

         $service->update([
             'average_rating' => $new_rating
         ]);

        $review->delete();

        return $this->respondWithSuccess(['message' => 'The review has been deleted', 'data' => $service]);
    }
}
