<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // 1. TAMPILKAN SEMUA USER
    public function index()
    {
        // Ambil semua data user, urutkan dari yang terbaru
        $users = User::latest()->get();
        return view('users.index', compact('users'));
    }

    // 2. FORM TAMBAH USER BARU
    public function create()
    {
        return view('users.create');
    }

    // 3. SIMPAN USER BARU KE DATABASE
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string'], // Pastikan kolom 'role' ada di database
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    // 4. FORM EDIT USER
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // 5. UPDATE DATA USER
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string'],
        ]);

        // Update data dasar
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Cek apakah password diganti?
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui!');
    }

    // 6. HAPUS USER
    public function destroy(User $user)
    {
        // Mencegah admin menghapus dirinya sendiri
        if (auth()->user()->id == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}