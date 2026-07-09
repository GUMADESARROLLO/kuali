<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Calendars/Index', [
            'calendars' => Calendar::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hours' => 'required|json',
        ]);

        Calendar::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'hours' => json_decode($validated['hours'], true),
            'is_active' => true,
        ]);

        return redirect()->route('admin.calendars.index')
            ->with('success', 'Calendario creado.');
    }

    public function update(Request $request, Calendar $calendar)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'hours' => 'required|json',
        ]);

        $calendar->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'hours' => json_decode($validated['hours'], true),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.calendars.index')
            ->with('success', 'Calendario actualizado.');
    }

    public function destroy(Calendar $calendar)
    {
        $calendar->delete();
        return redirect()->route('admin.calendars.index')
            ->with('success', 'Calendario eliminado.');
    }
}
