<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cluster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClusterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.clusters.index', ['clusters' => Cluster::orderBy('display_order')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.clusters.form', ['cluster' => new Cluster]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $cluster = Cluster::create([...$data, 'slug' => Str::slug($data['name'])]);
        AuditLog::record($request, 'cluster.created', $cluster, $cluster->toArray());

        return redirect()->route('admin.clusters.index')->with('status', 'Cluster created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cluster $cluster)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cluster $cluster): View
    {
        return view('admin.clusters.form', compact('cluster'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cluster $cluster): RedirectResponse
    {
        $data = $this->validated($request);
        $cluster->update([...$data, 'slug' => Str::slug($data['name'])]);
        AuditLog::record($request, 'cluster.updated', $cluster, $data);

        return redirect()->route('admin.clusters.index')->with('status', 'Cluster updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cluster $cluster)
    {
        //
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['required', 'string', 'max:2000'], 'display_order' => ['required', 'integer', 'between:1,255'], 'is_active' => ['required', 'boolean']]);
    }
}
