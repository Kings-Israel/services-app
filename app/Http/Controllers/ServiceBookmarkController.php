<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceBookmark;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Service Bookmarks APIs
 *
 * @group Service Bookmarking APIs
 */
class ServiceBookmarkController extends Controller
{
    use ApiResponseHelpers;

    /**
     * Get Bookmarked Services
     *
     * @authenticated
     *
     * @response 200
     * @responseParam data A list of the bookmarked services with the vendor information
     */
    public function bookmarks(Request $request): JsonResponse
    {
        $search_query = $request->query('q');
        $per_page = $request->query('per_page');

        if ($request->query('per_page')) {
            $bookmarks = ServiceBookmark::with('service.user')
                                        ->where('user_id', auth()->id())
                                        ->when($search_query && $search_query != '', function($query) use ($search_query) {
                                            $query->where(function($query) use ($search_query) {
                                                $query->whereHas('service', function($query) use ($search_query) {
                                                    $query->where('title', 'LIKE', '%'.$search_query.'%');
                                                });
                                            });
                                        })
                                        ->paginate($per_page);
        } else {
            $bookmarks = ServiceBookmark::with('service.user')->get();
        }

        return $this->respondWithSuccess(['data' => $bookmarks]);
    }

    /**
     * Get Bookmarked Service
     *
     * @urlParam ID The ID of the bookmark
     *
     * @response 200
     *
     * @responseParam data The Bookmarked Service with vendor information
     */
    public function bookmark($id): JsonResponse
    {
        $bookmark = ServiceBookmark::with('service.user')->find($id);

        return $this->respondWithSuccess(['data' => $bookmark]);
    }

    /**
     * Add or Remove a Service to and from bookmarked services
     *
     * @authenticated
     *
     * @bodyParam id string required The id of the service
     *
     * @response 200
     * @responseParam message Service Bookmarked successfully
     * @responseParam data The bookmarked service
     */
    public function bookmarkService(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required'
        ]);

        $service = Service::find($request->service_id);

        if (!$service) {
            return $this->respondNotFound('Service not found');
        }

        $bookmarked = ServiceBookmark::where('service_id', $request->service_id)->where('user_id', $request->user()->id)->first();

        if (!$bookmarked) {
            $request->user()->bookmarks()->create([
                'service_id' => $request->service_id
            ]);

            return $this->respondWithSuccess(['message' => 'Service bookmarked successfully', 'data' => $service]);
        } else {
            ServiceBookmark::where('service_id', $request->service_id)->where('user_id', $request->user()->id)->delete();

            return $this->respondWithSuccess(['message' => 'Service removed from bookmarks successfully', 'data' => $service]);
        }
    }
}
