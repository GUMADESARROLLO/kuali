<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['subcategories' => fn($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'is_active' => $cat->is_active,
                'sort_order' => $cat->sort_order,
                'subcategories' => $cat->subcategories->map(fn($sub) => [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'slug' => $sub->slug,
                    'is_active' => $sub->is_active,
                    'sort_order' => $sub->sort_order,
                ]),
            ]);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
            ],
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar una categoría con tickets asociados.');
        }
        $category->subcategories()->delete();
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría eliminada.');
    }

    // --- Subcategories ---

    public function storeSubcategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        Subcategory::create([
            'category_id' => $category->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Subcategoría creada.');
    }

    public function updateSubcategory(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $subcategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Subcategoría actualizada.');
    }

    public function reorderCategories(Request $request)
    {
        $ids = $request->input('ids', []);
        foreach ($ids as $i => $id) {
            Category::where('id', $id)->update(['sort_order' => $i]);
        }
        return redirect()->route('admin.categories.index')->with('success', 'Orden actualizado.');
    }

    public function reorderSubcategories(Request $request)
    {
        $ids = $request->input('ids', []);
        foreach ($ids as $i => $id) {
            Subcategory::where('id', $id)->update(['sort_order' => $i]);
        }
        return redirect()->route('admin.categories.index')->with('success', 'Orden actualizado.');
    }

    public function destroySubcategory(Subcategory $subcategory)
    {
        if ($subcategory->tickets()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar una subcategoría con tickets asociados.');
        }
        $subcategory->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Subcategoría eliminada.');
    }
}
