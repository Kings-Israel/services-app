<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Notifications\ServiceRequest as NotificationsServiceRequest;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

/**
 * @group Services APIs
 *
 * @authenticated
 */
class ServiceRequestController extends Controller
{
    use ApiResponseHelpers;

    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $search = $request->query('q');

        if ($request->query('per_page')) {
            $service_requests = ServiceRequest::with('user', 'service.user')
                                                ->when(auth()->user()->hasRole('user'), function ($query) {
                                                    $query->whereHas('user', function ($query) {
                                                        $query->where('id', auth()->id());
                                                    });
                                                })
                                                ->when(auth()->user()->hasRole('vendor'), function ($query) {
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
            $service_requests = ServiceRequest::with('user', 'service.user')
                                                ->when(auth()->user()->hasRole('user'), function ($query) {
                                                    $query->whereHas('user', function ($query) {
                                                        $query->where('id', auth()->id());
                                                    });
                                                })
                                                ->when(auth()->user()->hasRole('vendor'), function ($query) {
                                                    $vendor_services = auth()->user()->services->pluck('id');
                                                    $query->whereIn('service_id', $vendor_services);
                                                })
                                                ->orderBy('created_at', 'DESC')
                                                ->get();
        }

        return $this->respondWithSuccess(['data' => $service_requests]);
    }

    /**
     * Request for a service
     *
     * @bodyParam service_id string required The id of the service
     * @bodyParam message string An optional message for the service provider
     *
     * @response 200
     *
     * @responseParam data The details of the service
     * @responseParam message The request was sent successfully
     */
    public function requestService(Request $request)
    {
        $request->validate([
            'service_id' => 'required',
            'message' => 'nullable|string'
        ]);

        $service = Service::with('categories', 'images', 'user')->find($request->service_id);

        if (!$service) {
            return $this->respondNotFound('Service not found');
        }

        if ($service->user->id == auth()->id()) {
            return $this->respondForbidden('You cannot request your own service');
        }

        ServiceRequest::create([
            'user_id' => auth()->id(),
            'service_id' => $service->id
        ]);

        $service->user->notify(new NotificationsServiceRequest(auth()->user(), $service));

        // TODO: add mobile notification if device token is available

        return $this->respondWithSuccess(['messaage' => 'Service request sent successfully', 'data' => $service]);
    }

    /**
     * Change the status of a service delivery request
     *
     * @bodyParam service_request_id string required The ID of the service request to be changed
     * @bodyParam service_id string required The ID of the service
     * @bodyParam status string required The new status of the service(Completed/Delivered, Delete, Dispute)
     *
     * @response 200
     * @responseParam data The service details
     * @responseParam message The updated status of the service delivery request
     */
    public function updateServiceRequestStatus(Request $request)
    {
        $request->validate([
            'service_request_id' => 'required',
            'status' => 'required'
        ]);

        $service_request = ServiceRequest::with('service.categories', 'service.images', 'service.user')->find($request->service_request_id);
        if (!$service_request) {
            return $this->respondNotFound('The service request does not exist');
        }

        $service = Service::with('categories', 'images', 'user')->find($service_request->service_id);

        if (!$service) {
            return $this->respondNotFound('The service does not exist');
        }

        if ($service_request->user_id != auth()->id()) {
            return $this->respondForbidden('Unauthorized action');
        }

        $allowed_statuses = ['completed', 'delivered', 'delete', 'dispute'];

        $status = strtolower($request->status);

        if (!collect($allowed_statuses)->contains($status)) {
            return $this->respondError('Please select Completed/Delivered or Delete or Dispute');
        }

        if ($status === 'delete') {
            $service_request->delete();

            // TODO: Add notification to the service provider of deletion

            return $this->respondWithSuccess(['message' => 'Service request deleted successfully', 'data' => $service]);
        } else {
            if ($status === 'completed' || $status == 'delivered') {
                $service_request->update([
                    'status' => 'Completed',
                ]);
                // TODO: Add notification to the service provider of completion
            } elseif ($status === 'dispute') {
                $service_request->update([
                    'status' => 'Disputed',
                ]);
                // TODO: Add notification to the service provider of dispute
            }

            // $service_request = ServiceRequest::with('service.categories', 'service.images', 'service.user')->find($request->service_request_id);

            return $this->respondWithSuccess(['message' => 'The service request status was updated successfully', 'data' => $service_request]);
        }
    }
}
