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
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => $categories
        ]);
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

        if ($request->hasFile('category_icon')) {
            $validated['category_icon'] = $request->file('category_icon')->store('categories/icons', 'public');
        }

        if ($request->hasFile('page_title_background')) {
            $validated['page_title_background'] = $request->file('page_title_background')->store('categories/backgrounds', 'public');
        }

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully.',
            'data' => $category->load('parent', 'children')
        ]);
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
            $old = $category->getRawOriginal('thumbnail');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('categories', 'public');
        }

        if ($request->hasFile('category_icon')) {
            $old = $category->getRawOriginal('category_icon');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $validated['category_icon'] = $request->file('category_icon')->store('categories/icons', 'public');
        }

        if ($request->hasFile('page_title_background')) {
            $old = $category->getRawOriginal('page_title_background');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $validated['page_title_background'] = $request->file('page_title_background')->store('categories/backgrounds', 'public');
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Delete all associated files
        foreach (['thumbnail', 'category_icon', 'page_title_background'] as $fileField) {
            $path = $category->getRawOriginal($fileField);
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }
}
