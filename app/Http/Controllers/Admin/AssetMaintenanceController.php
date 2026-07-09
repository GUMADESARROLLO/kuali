<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetMaintenance::with(['asset.company', 'asset.category']);

        if ($request->filled('asset_id')) $query->where('asset_id', $request->asset_id);
        if ($request->filled('maintenance_type')) $query->where('maintenance_type', $request->maintenance_type);
        if ($request->filled('date_from')) $query->whereDate('performed_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('performed_at', '<=', $request->date_to);

        return Inertia::render('Admin/Maintenance/Index', [
            'records' => $query->latest('performed_at')->paginate(20),
            'assets' => Asset::with('company')->orderBy('asset_tag')->get(['id', 'asset_tag', 'name', 'company_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_type' => 'required|string|max:100',
            'component' => 'nullable|string|max:255',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'performed_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        AssetMaintenance::create($validated);

        return redirect()->back()->with('success', 'Mantenimiento registrado.');
    }

    public function update(Request $request, AssetMaintenance $assetMaintenance)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|string|max:100',
            'component' => 'nullable|string|max:255',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string|max:255',
            'performed_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $assetMaintenance->update($validated);

        return redirect()->back()->with('success', 'Mantenimiento actualizado.');
    }

    public function destroy(AssetMaintenance $assetMaintenance)
    {
        $assetMaintenance->delete();
        return redirect()->back()->with('success', 'Mantenimiento eliminado.');
    }
}
