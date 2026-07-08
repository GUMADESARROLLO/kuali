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
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Departments/Form');
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
            'name' => 'required|string|max:255|unique:departments,name',
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
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
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
