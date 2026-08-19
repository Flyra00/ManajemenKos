<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;


class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $facilities = Facility::latest()
        ->paginate(10);
        return view("facilities.index", compact("facilities"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $facilities = Facility::all();
        return view("facilities.create", compact("facility"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'unique',
            'description'=> 'nulllable',
        ]);

        Facility::create($validated);
        return redirect()
        ->route('facilities.index')
        ->with('success','fasilitas berhasil ditambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility)
    {
        //
        return view('facilities.show', compact('facility'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Facility $facility)
    {
        //
        return view('facilities.edit', compact('facility'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        //
        $facilities = Facility::all();
        $validated = $request->validate([
            'name'=>['required','string'],
            'description'=> ['nullable','string'],
        ]);

        Facility::updated($validated);

        return redirect()
        ->route('facilities')
        ->with('success','fasilitas berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        //
        $facility->delete();

        return redirect()
        ->route('facilities.index')
        ->with('success','fasilitas berhasil dihapus');
    }
}
