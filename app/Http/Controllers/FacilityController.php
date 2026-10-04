<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Facility::withCount('rooms')->latest();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $facilities = $query->paginate(10)->withQueryString();

        $stats = [
            'total'      => Facility::count(),
            'used'       => Facility::has('rooms')->count(),
            'assignments'=> \Illuminate\Support\Facades\DB::table('room_facilities')->count(),
        ];

        return view('facilities.index', compact('facilities', 'stats'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("facilities.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:facilities,name'],
            'description' => ['nullable', 'string'],
        ]);

        Facility::create($validated);

        return redirect()
            ->route('facilities.index')
            ->with('success', 'Fasilitas berhasil ditambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility)
    {
        $facility->loadCount('rooms')->load('rooms');
        return view('facilities.show', compact('facility'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facility $facility)
    {
        return view('facilities.edit', compact('facility'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('facilities', 'name')->ignore($facility->id)],
            'description' => ['nullable', 'string'],
        ]);

        $facility->update($validated);

        return redirect()
            ->route('facilities.index')
            ->with('success', 'Fasilitas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()
            ->route('facilities.index')
            ->with('success', 'Fasilitas berhasil dihapus');
    }
}

