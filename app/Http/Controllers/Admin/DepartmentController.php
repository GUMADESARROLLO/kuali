<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Departments/Index', [
            'departments' => Department::with('company')->orderBy('name')->get(),
            'companies' => \App\Models\Company::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Departments/Form', [
            'companies' => \App\Models\Company::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Department $department)
    {
        return Inertia::render('Admin/Departments/Form', [
            'department' => $department,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento creado.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento actualizado.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar un departamento con usuarios asociados.');
        }
        $department->delete();
        return redirect()->route('admin.departments.index')
            ->with('success', 'Departamento eliminado.');
    }
}
