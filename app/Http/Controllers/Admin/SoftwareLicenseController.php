<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Company;
use App\Models\SoftwareLicense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SoftwareLicenseController extends Controller
{
    public function index(Request $request)
    {
        $query = SoftwareLicense::with('company');

        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('vendor', 'like', "%{$s}%");
            });
        }

        return Inertia::render('Admin/Licenses/Index', [
            'licenses' => $query->orderBy('name')->paginate(20),
            'companies' => Company::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'license_key' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'seats_total' => 'required|integer|min:1',
            'seats_used' => 'required|integer|min:0|lte:seats_total',
            'recurring' => 'boolean',
            'billing_period' => 'nullable|string|in:mensual,trimestral,semestral,anual',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:purchase_date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        SoftwareLicense::create($validated);

        return redirect()->route('admin.licenses.index')->with('success', 'Licencia creada.');
    }

    public function update(Request $request, SoftwareLicense $softwareLicense)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_key' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'seats_total' => 'required|integer|min:1',
            'seats_used' => 'required|integer|min:0|lte:seats_total',
            'recurring' => 'boolean',
            'billing_period' => 'nullable|string|in:mensual,trimestral,semestral,anual',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:purchase_date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $softwareLicense->update($validated);

        return redirect()->route('admin.licenses.index')->with('success', 'Licencia actualizada.');
    }

    public function destroy(SoftwareLicense $softwareLicense)
    {
        $softwareLicense->delete();
        return redirect()->route('admin.licenses.index')->with('success', 'Licencia eliminada.');
    }

    public function assign(Request $request, SoftwareLicense $softwareLicense)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'installed_at' => 'nullable|date',
        ]);

        $softwareLicense->assets()->attach($validated['asset_id'], [
            'installed_at' => $validated['installed_at'] ?? now(),
        ]);

        $softwareLicense->increment('seats_used');

        return redirect()->back()->with('success', 'Licencia asignada al activo.');
    }

    public function revoke(SoftwareLicense $softwareLicense, Asset $asset)
    {
        $softwareLicense->assets()->detach($asset->id);
        $softwareLicense->decrement('seats_used');

        return redirect()->back()->with('success', 'Licencia revocada del activo.');
    }
}
