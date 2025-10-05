<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Status;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statuses = Status::with('creator')->orderBy('name')->get();
        
        return Inertia::render('Statuses/Index', [
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Statuses/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:statuses,name',
        ]);
        
        Status::create([
            'name' => $request->name,
            'created_by' => auth()->id(),
        ]);
        
        return redirect()->route('statuses.index')->with('success', 'Status created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Status $status)
    {
        $status->load(['creator', 'leads']);
        
        return Inertia::render('Statuses/Show', [
            'status' => $status,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Status $status)
    {
        return Inertia::render('Statuses/Edit', [
            'status' => $status,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Status $status)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:statuses,name,' . $status->id,
        ]);
        
        $status->update([
            'name' => $request->name,
        ]);
        
        return redirect()->route('statuses.index')->with('success', 'Status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status)
    {
        // Check if status is being used by any leads
        if ($status->leads()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete status that is being used by leads.');
        }
        
        $status->delete();
        
        return redirect()->route('statuses.index')->with('success', 'Status deleted successfully.');
    }
}
