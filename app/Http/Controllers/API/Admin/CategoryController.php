<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::with('parent')->get();
        return response()->json($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryStoreRequest $request)
    {
        $validated = $request->validated();
        
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('categories', 'public');
        }

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return response()->json($category->load('parent', 'children'));
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $validated = $request->validated();

        if (empty($validated['slug']) && isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            $oldThumbnail = $category->getRawOriginal('thumbnail');
            if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
                Storage::disk('public')->delete($oldThumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('categories', 'public');
        }

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        $oldThumbnail = $category->getRawOriginal('thumbnail');
        if ($oldThumbnail && Storage::disk('public')->exists($oldThumbnail)) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
