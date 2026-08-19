<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $rooms = Room::with('facilities')
        ->latest()
        ->paginate(10);

        return view('rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $facilities = facility::all();
        return view('rooms.create', compact('facilities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'room_number' => [
                'required',
                'unique:rooms,room_number'
            ],

            'floor'=> [
                'nullable',
                'string',
            ],

            'price' =>[
                'required',
                'numeric'
            ],

            'status'=> [
                'required',
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'image'=> [
                'nullable',
                'image',
                'max:2048',
            ],

            'facilities'=> [
                'nullable',
                'array'
            ],

            'facilities.*'=> [
                'exists:facilities,id',
            ],
        ]);

        //upload img
        $imagePath  = null;

        if ($request->hasFile('image')) {
            $imagePath = $request
            ->file('image')
            ->store('room','public');
        }

        //create room
        $room = Room::create([
            'room_number' => $validated['room_number'],
            'floor' => $validated['floor'] ?? null,
            'price' => $validated['price'],
            'status' => $validated['status'],
            'is_active' => true,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
        ]);

        //simpan fasilitas
        if($request->facilities){
            $room->facilities()
            ->sync($request->facilities);
        }

        return redirect()
        ->route('rooms.index')
        ->with('success','kamar berhasil ditambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        //
        $room->load('facilities');
        return view('rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        //
        $facilities = Facility::all();
        $room->load('facilities');

        return view('rooms.edit',compact(
            'room',
            'facilities'
        ));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        //
        $request->validate([
            'room_number' => [
                'required',
                'unique:rooms,room_number,'.$room->id
            ],

            'floor'=> [
                'nullable',
                'string',
            ],

            'price' =>[
                'required',
                'numeric'
            ],

            'status'=> [
                'required',
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'image'=> [
                'nullable',
                'image',
                'max:2048',
            ],

            'facilities'=> [
                'nullable',
                'array'
            ],

            'facilities.*'=> [
                'exists:facilities,id',
            ],
        ]);
                //upload img
        $imagePath  = $room->image;

        if ($request->hasFile('image')) {

            if($room->image) {
                Storage::disk('public')
                ->delete($room->image);
            }


            $imagePath = $request
            ->file('image')
            ->store('room','public');
        }

        //updt room
        $room ->update([
            'room_number' => $request->room_number,
            'floor' => $request->floor,
            'price' => $request->price,
            'status'=> $request->status,
            'description'=> $request->description,
            'image'=> $imagePath,
        ]);

        //simpan fasilitas

        $room -> facilities()
            ->sync($request->facilities ?? []);


        return redirect()
        ->route('rooms.index')
        ->with('success','kamar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        //
        if($room->image){
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()
        ->route('rooms.index')
        ->with('success','kamar berhasil di hapus');
    }
}
