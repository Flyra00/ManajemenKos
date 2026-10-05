<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'    => ['required', 'string', 'max:20', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            'phone.required'    => 'Nomor telepon wajib diisi.',
            'phone.unique'      => 'Nomor telepon ini sudah terdaftar. Silakan gunakan nomor telepon lain.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal terdiri dari 8 karakter.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $user->assignRole('tenant');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
