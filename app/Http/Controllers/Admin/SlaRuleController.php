<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use App\Models\SlaRule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SlaRuleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/SlaRules/Index', [
            'rules' => SlaRule::with('calendar')->orderBy('sort_order')->get(),
            'calendars' => Calendar::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calendar_id' => 'required|exists:calendars,id',
            'conditions' => 'nullable|array',
            'first_response_hours' => 'nullable|integer|min:0',
            'update_hours' => 'nullable|integer|min:0',
            'solution_hours' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        SlaRule::create($validated);

        return redirect()->route('admin.sla-rules.index')
            ->with('success', 'Regla SLA creada.');
    }

    public function update(Request $request, SlaRule $slaRule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calendar_id' => 'required|exists:calendars,id',
            'conditions' => 'nullable|array',
            'first_response_hours' => 'nullable|integer|min:0',
            'update_hours' => 'nullable|integer|min:0',
            'solution_hours' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $slaRule->update($validated);

        return redirect()->route('admin.sla-rules.index')
            ->with('success', 'Regla SLA actualizada.');
    }

    public function destroy(SlaRule $slaRule)
    {
        $slaRule->delete();
        return redirect()->route('admin.sla-rules.index')
            ->with('success', 'Regla SLA eliminada.');
    }
}
