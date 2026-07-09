<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Companies/Index', [
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Company::create($validated);

        return redirect()->route('admin.companies.index')->with('success', 'Empresa creada.');
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.index')->with('success', 'Empresa actualizada.');
    }

    public function destroy(Company $company)
    {
        if ($company->assets()->exists()) {
            return redirect()->route('admin.companies.index')->with('error', 'No se puede eliminar: tiene activos asociados.');
        }
        $company->delete();
        return redirect()->route('admin.companies.index')->with('success', 'Empresa eliminada.');
    }
}
