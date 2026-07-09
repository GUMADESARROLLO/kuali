<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $query = Person::with(['company', 'department']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }
        if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);

        return Inertia::render('Admin/Persons/Index', [
            'persons' => $query->orderBy('last_name')->orderBy('first_name')->paginate(20),
            'companies' => Company::active()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::with('company')->orderBy('name')->get(['id', 'name', 'company_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:people,email',
            'phone' => 'nullable|string|max:30',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        Person::create($validated);

        return redirect()->route('admin.persons.index')->with('success', 'Persona creada.');
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:people,email,' . $person->id,
            'phone' => 'nullable|string|max:30',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $person->update($validated);

        return redirect()->route('admin.persons.index')->with('success', 'Persona actualizada.');
    }

    public function destroy(Person $person)
    {
        $person->delete();
        return redirect()->route('admin.persons.index')->with('success', 'Persona eliminada.');
    }
}
