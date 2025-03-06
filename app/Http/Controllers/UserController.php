<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //collection with multiple where conditions
        

        $filters = request()->all('search');
        return Inertia::render('users/Index', [
            'users' => User::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|lowercase|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return to_route('user.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //if password is '' then don't update password
        if($request->password == ''){
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|lowercase|max:255|unique:'.User::class.','.$user->id,
            ]);
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'role' => $request->role,
            ]);
        }else{
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|lowercase|max:255|unique:'.User::class.','.$user->id,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);
        }
        return to_route('user.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Approve the specified resource.
     */
    public function approve(User $user)
    {
        $user->status = ($user->status == 'Approved' ? 'Pending' : 'Approved');
        $user->save();
        return redirect()->route('user.index');
    }
}
