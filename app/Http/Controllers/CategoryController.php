<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category; // Assuming you have a Category model

class CategoryController extends Controller
{
    public function addCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        try {
            $category = new Category();
            $category->name = $request->input('name');
            $category->description = $request->input('description', '');
            $category->icon = $request->input('icon', '');
            $category->icon_alt = $request->input('icon_alt', '');
            $category->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Category added successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add category',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getAllCategories()
    {
        try {
            $categories = Category::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Categories fetched successfully',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
