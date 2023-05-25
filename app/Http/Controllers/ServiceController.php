<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceImage;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * @group Services APIs
 */
class ServiceController extends Controller
{
    use ApiResponseHelpers;
    /**
     * Get all services.
     *
     * @response 200
     * @responseField content List of all services
     */
    public function index(Request $request, $latitude = NULL, $longitude = NULL)
    {
        $per_page = $request->query('per_page');
        $search_query = $request->query('search_query');

        if ($request->query('per_page')) {
            $services = Service::with('images', 'categories', 'user', 'reviews')
                        ->when($search_query && $search_query != '', function($query) use ($search_query) {
                            $query->where('title', 'LIKE', '%'.$search_query.'%');
                        })
                        ->paginate($per_page);
        } else {
            $services = Service::with('images', 'categories', 'user', 'reviews')->get();
        }

        return request()->wantsJson()
            ? $this->respondWithSuccess(['data' => $services])
            : view('', compact('services'));
    }

    /**
     * Add a new service.
     * @authenticated
     *
     * @bodyParam title string required The title of the new service
     * @bodyParam description text The description of the new service
     * @bodyParam price string required The price of the new service
     * @bodyParam location_lat string required The latitude location of the new service
     * @bodyParam location_long string required The longitude location of the new service
     * @bodyParam categories array required The category/categories of the new service
     *
     * @response 201
     * @responseField data Details of the added service
     */
    public function store(StoreServiceRequest $request)
    {
        $service_location = Http::withOptions(['verify' => false])->get('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$request->location_lat.','.$request->location_long.'&key=AIzaSyCisnVFSnc5QVfU2Jm2W3oRLqMDrKwOEoM');

        $location = $service_location['results'][0]['formatted_address'];

        $service = auth()->user()->services()->create([
            'title' => $request->title,
            'price_min' => $request->price_min,
            'price_max' => $request->has('price_max') && $request->price_max != '' ? $request->price_max : NULL,
            'location' => $location,
            'location_lat' => $request->location_lat,
            'location_long' => $request->location_long,
        ]);

        collect($request->categories)->each(function ($category) use ($service) {
            ServiceCategory::create([
                'service_id' => $service->id,
                'category_id' => $category,
            ]);
        });

        return $request->wantsJson()
            ? $this->respondCreated($service->load('categories', 'images'))
            : view('', compact('service'));
    }

    /**
     * Show service details.
     *
     * @urlParam id The id of the service
     *
     * @response 200
     * @responseField data The service details
     */
    public function show($id)
    {
        $service = Service::with('categories', 'images', 'reviews')->find($id);

        return request()->wantsJson()
            ? $this->respondeWithSuccess($service)
            : view('', compact('service'));
    }

    /**
     * Update a service.
     *
     * @authenticated
     *
     * @bodyParam title string required The title of the service
     * @bodyParam description string The description of the service
     * @bodyParam price string The price of the service
     * @bodyParam location_lat string The latitude location of the new service
     * @bodyParam location_long string The longitude location of the new service
     *
     * @urlParam id The ID of the service
     *
     * @response 200
     * @responseField content The details of the updated service
     */
    public function update(StoreServiceRequest $request, $id)
    {
        $service_location = Http::withOptions(['verify' => false])->get('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$request->location_lat.','.$request->location_long.'&key=AIzaSyCisnVFSnc5QVfU2Jm2W3oRLqMDrKwOEoM');

        $location = $service_location['results'][0]['formatted_address'];

        $service = Service::find($id);

        $service->update([
            'title' => $request->title,
            'price_min' => $request->price_min,
            'price_max' => $request->has('price_max') && $request->price_max != '' ? $request->price_max : NULL,
            'location' => $location,
            'location_lat' => $request->location_lat,
            'location_long' => $request->location_long,
        ]);

        ServiceCategory::where('service_id', $id)->delete();

        collect($request->categories)->each(function ($category) use ($service) {
            ServiceCategory::create([
                'service_id' => $service->id,
                'category_id' => $category,
            ]);
        });

        return $request->wantsJson()
            ? $this->respondWithSuccess(['data' => $service->load('categories', 'images')])
            : view('', compact('service'));
    }

    /**
     * Delete a service.
     *
     * @authenticated
     *
     * @urlParam id The id of the service
     *
     * @response 200
     * @responseField content The details of the deleted service
     */
    public function destroy($id)
    {
        $service = Service::with('images')->find($id);
    }

    /**
     * Add services images
     *
     * @urlParam id The id of the service
     * @requestBody images file required The images as an array
     *
     * @response 200
     * @responseBody message The images were successfully added
     * @responseBody data The service details
     */
    public function saveServiceImages(Request $request, $id)
    {
        abort_if(auth()->check() && auth()->user()->hasRole('user'), 401, 'Permission denied.');

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'mimes:png,jpg,jpeg'
        ]);

        $service = Service::find($id);

        if (!$service) {
            return $this->respondNotFound('Service not found');
        }

        foreach($request->images as $image) {
            $service->images()->create([
                'url' => config('app.url').'storage/service/images/' . pathinfo($image->store('images', 'service'), PATHINFO_BASENAME),
            ]);
        }

        $service = Service::with('images', 'categories')->find($id);

        return request()->wantsJson()
            ? $this->respondCreated(['data' => $service])
            : view('', compact('service'));
    }
}
