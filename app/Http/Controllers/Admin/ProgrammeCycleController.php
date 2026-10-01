<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProgrammeCycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgrammeCycleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.cycles.index', ['cycles' => ProgrammeCycle::orderByDesc('starts_on')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.cycles.form', ['cycle' => new ProgrammeCycle]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $cycle = ProgrammeCycle::create($this->validated($request));
        AuditLog::record($request, 'cycle.created', $cycle, $cycle->toArray());

        return redirect()->route('admin.cycles.index')->with('status', 'Programme cycle created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgrammeCycle $programmeCycle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProgrammeCycle $cycle): View
    {
        return view('admin.cycles.form', compact('cycle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgrammeCycle $cycle): RedirectResponse
    {
        $before = $cycle->toArray();
        $cycle->update($this->validated($request));
        AuditLog::record($request, 'cycle.updated', $cycle, ['before' => $before, 'after' => $cycle->fresh()->toArray()]);

        return redirect()->route('admin.cycles.index')->with('status', 'Programme cycle updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgrammeCycle $programmeCycle)
    {
        //
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:120'], 'starts_on' => ['required', 'date'], 'ends_on' => ['required', 'date', 'after:starts_on'], 'status' => ['required', 'in:draft,open,active,closed,archived'], 'target_participants' => ['required', 'integer', 'between:1,5000']]);
    }
}
