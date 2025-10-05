<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('permissions')->orderBy('created_at', 'desc')->get();
        $permissions = Permission::all()->groupBy('module');

        return Inertia::render('Users/Index', [
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('module');

        return Inertia::render('Users/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Only admins can create users
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can create users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        // Assign permissions if provided
        if ($request->has('permissions') && is_array($request->permissions)) {
            $newUser->permissions()->attach($request->permissions);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load('permissions');

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $user->load('permissions');
        $permissions = Permission::all()->groupBy('module');

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Handle user information update
        if ($request->has('name') || $request->has('email') || $request->has('role') || $request->has('password')) {
            $validationRules = [];
            
            if ($request->has('name')) {
                $validationRules['name'] = 'required|string|max:255';
            }
            
            if ($request->has('email')) {
                $validationRules['email'] = 'required|string|email|max:255|unique:users,email,' . $user->id;
            }
            
            if ($request->has('role')) {
                // Only admins can change roles
                if (!$currentUser->isAdmin()) {
                    abort(403, 'Only administrators can change user roles.');
                }
                $validationRules['role'] = 'required|in:admin,user';
            }
            
            if ($request->has('password')) {
                $validationRules['password'] = 'required|string|min:8|confirmed';
            }

            $request->validate($validationRules);

            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }
            
            if ($request->has('role')) {
                $updateData['role'] = $request->role;
            }
            
            if ($request->has('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }
        }

        // Handle permissions update
        if ($request->has('permissions')) {
            $request->validate([
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            $user->permissions()->sync($request->permissions);
        }

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();

        // Only admins can delete users
        if (!$currentUser->isAdmin()) {
            abort(403, 'Only administrators can delete users.');
        }

        // Prevent admin from deleting themselves
        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
