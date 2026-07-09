<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Department;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with(['company', 'category', 'assignedPerson', 'parent', 'children']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('asset_tag', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('serial_number', 'like', "%{$s}%");
            });
        }
        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('category_id')) $query->where('asset_category_id', $request->category_id);
        if ($request->filled('status')) $query->where('status', $request->status);

        return Inertia::render('Admin/Assets/Index', [
            'assets' => $query->orderBy('asset_tag')->paginate(20),
            'companies' => Company::active()->orderBy('name')->get(['id', 'name']),
            'categories' => AssetCategory::active()->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'company', 'category', 'assignedPerson.company', 'assignedPerson.department', 'parent', 'children',
            'children.category', 'children.assignedPerson',
            'maintenance' => fn($q) => $q->latest('performed_at'),
            'softwareLicenses',
        ]);

        return Inertia::render('Admin/Assets/Show', [
            'asset' => $asset,
            'people' => Person::where('is_active', true)->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'categories' => AssetCategory::active()->orderBy('sort_order')->get(['id', 'name']),
            'companies' => Company::active()->orderBy('name')->get(['id', 'name']),
            'licenses' => \App\Models\SoftwareLicense::when($asset->company_id, fn($q) => $q->where('company_id', $asset->company_id))
                ->orderBy('name')->get(['id', 'name', 'seats_total', 'seats_used']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Assets/Form', [
            'nextCode' => $this->nextAssetCode(),
            'categories' => AssetCategory::active()->orderBy('sort_order')->get(['id', 'name']),
            'people' => Person::with('company', 'department')->where('is_active', true)->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'company_id', 'department_id']),
            'assets' => Asset::orderBy('asset_tag')->get(['id', 'asset_tag', 'name']),
        ]);
    }

    private function nextAssetCode(): string
    {
        $last = Asset::lockForUpdate()->max('id') ?? 0;
        return str_pad($last + 1, 6, '0', STR_PAD_LEFT);
    }

    public function edit(Asset $asset)
    {
        return Inertia::render('Admin/Assets/Form', [
            'asset' => $asset,
            'categories' => AssetCategory::active()->orderBy('sort_order')->get(['id', 'name']),
            'people' => Person::with('company', 'department')->where('is_active', true)->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'company_id', 'department_id']),
            'assets' => Asset::where('id', '!=', $asset->id)->orderBy('asset_tag')->get(['id', 'asset_tag', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'parent_asset_id' => 'nullable|exists:assets,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:255',
            'status' => 'required|in:disponible,asignado,en_reparacion,dado_de_baja',
            'person_id' => 'required|exists:people,id',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Asset::create(array_merge($validated, ['asset_tag' => $this->nextAssetCode()]));

        return redirect()->route('admin.assets.index')->with('success', 'Activo creado.');
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'parent_asset_id' => 'nullable|exists:assets,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:255',
            'status' => 'required|in:disponible,asignado,en_reparacion,dado_de_baja',
            'person_id' => 'required|exists:people,id',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('admin.assets.index')->with('success', 'Activo actualizado.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('admin.assets.index')->with('success', 'Activo eliminado.');
    }

    public function assign(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'person_id' => 'nullable|exists:people,id',
        ]);

        $asset->update(['person_id' => $validated['person_id'], 'assigned_at' => $validated['person_id'] ? now() : null, 'status' => $validated['person_id'] ? 'asignado' : 'disponible']);

        foreach ($asset->children as $child) {
            $child->update(['person_id' => $validated['person_id'], 'status' => $validated['person_id'] ? 'asignado' : 'disponible']);
        }

        return redirect()->back()->with('success', 'Asignación actualizada.');
    }
}
