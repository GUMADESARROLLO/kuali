<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SubcategoryController extends Controller
{
    public function byCategory(Category $category): JsonResponse
    {
        return response()->json(
            $category->subcategories()->active()->orderBy('sort_order')->get(['id', 'name'])
        );
    }
}
