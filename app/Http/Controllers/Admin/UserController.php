<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('department', 'roles')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'is_active' => $u->is_active,
                'department_id' => $u->department_id,
                'department' => $u->department?->name,
                'roles' => $u->getRoleNames()->implode(', '),
                'role' => $u->getRoleNames()->first(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'departments' => Department::with('company')->active()->orderBy('company_id')->orderBy('name')->get(['id', 'name', 'company_id']),
            'roles' => Role::all()->pluck('name'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Form', [
            'departments' => Department::with('company')->active()->orderBy('company_id')->orderBy('name')->get(['id', 'name', 'company_id']),
            'roles' => Role::all()->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department_id' => $validated['department_id'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'department_id' => $user->department_id,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'role' => $user->getRoleNames()->first(),
            ],
            'departments' => Department::with('company')->active()->orderBy('company_id')->orderBy('name')->get(['id', 'name', 'company_id']),
            'roles' => Role::all()->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'role' => 'required|exists:roles,name',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado.');
    }
}
