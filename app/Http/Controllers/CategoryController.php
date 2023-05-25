<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

/**
 * Categories APIs
 *
 * @group Categories APIs
 */
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

        if ($request->query('per_page')) {
            $categories = Category::with('services.images', 'services.categories', 'services.user')
                        ->when($search_query && $search_query != '', function($query) use ($search_query) {
                            $query->where('name', 'LIKE', '%'.$search_query.'%');
                        })
                        ->paginate($per_page);
        } else {
            $categories = Category::with('services.images', 'services.categories', 'services.user')->get();
        }

        return $this->respondWithSuccess(['data' => $categories]);
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
    public function show($id)
    {
        $category = Category::with('services.images', 'services.user')->find($id);

        return $this->respondWithSuccess(['data' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update([
            'name' => $request->name,
            'image' => $request->hasFile('image') ? config('app.url').'storage/category/images/'.pathinfo($request->image->store('images', 'category'), PATHINFO_BASENAME) : $category->image,
        ]);

        return $this->respondWithSuccess(['data' => $category->load('services.images', 'services.category', 'services.user')]);
    }

    public function destroy(Category $category)
    {
        //
    }
}
