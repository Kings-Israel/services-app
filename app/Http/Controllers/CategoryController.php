<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseHelpers;

    /**
     * List all the categories.
     *
     * @response 200
     * @responseParam data List of categories
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $search_query = $request->query('search_query');

        $users = Category::with('services.images', 'services.categories')
                        ->when($search_query && $search_query != '', function($query) use ($search_query) {
                            $query->where('name', 'LIKE', '%'.$search_query.'%');
                        })
                        ->paginate($per_page);

        if ($request->per_page) {
            $users = Category::with('services.images', 'services.categories')
                        ->when($search_query && $search_query != '', function($query) use ($search_query) {
                            $query->where('name', 'LIKE', '%'.$search_query.'%');
                        })
                        ->paginate($per_page);
        } else {
            $users = Category::with('services.images', 'services.categories')->get();
        }

        return $this->respondWithSuccess(['data' => $users]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            'name' => $request->name,
            'image' => $request->hasFile('image') ? config('app.url').'storage/category/images/'.pathinfo($request->image->store('images', 'category'), PATHINFO_BASENAME) : NULL,
        ]);

        return $this->respondCreated(['data' => $category]);
    }

    /**
     * Get Category Details.
     *
     * @urlParam id The ID of the category
     * @response 200
     * @responseParam data The category
     */
    public function show(Category $category)
    {
        return $this->respondWithSuccess(['data' => $category->load('services.images')]);
    }

    public function update(Request $request, Category $category)
    {
        //
    }

    public function destroy(Category $category)
    {
        //
    }
}
