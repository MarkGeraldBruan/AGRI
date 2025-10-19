<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Only allow admin users to access user management
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized access. Admin privileges required.'], 403);
                }
                abort(403, 'Unauthorized access. Admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['user'] = User::all();
        return view('client.users.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('client.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        // If admin role, set all permissions to true
        if ($validated['role'] === 'admin') {
            $validated['can_create'] = true;
            $validated['can_read'] = true;
            $validated['can_update'] = true;
            $validated['can_delete'] = true;
            $validated['can_stock_in'] = true;
            $validated['can_stock_out'] = true;
        } else {
            // For regular users, use submitted values or defaults
            $validated['can_create'] = $request->boolean('can_create', false);
            $validated['can_read'] = $request->boolean('can_read', true);
            $validated['can_update'] = $request->boolean('can_update', false);
            $validated['can_delete'] = $request->boolean('can_delete', false);
            $validated['can_stock_in'] = $request->boolean('can_stock_in', false);
            $validated['can_stock_out'] = $request->boolean('can_stock_out', false);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $validated['status'] ?? 'active';

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User has been successfully created!');
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
    public function edit(string $id)
    {
        $data['user'] = User::findOrFail($id);
        return view('client.users.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);
        
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'] ?? 'active';
        
        // If admin role, set all permissions to true
        if ($validated['role'] === 'admin') {
            $user->can_create = true;
            $user->can_read = true;
            $user->can_update = true;
            $user->can_delete = true;
            $user->can_stock_in = true;
            $user->can_stock_out = true;
        } else {
            // For regular users, use submitted values
            $user->can_create = $request->boolean('can_create', false);
            $user->can_read = $request->boolean('can_read', true);
            $user->can_update = $request->boolean('can_update', false);
            $user->can_delete = $request->boolean('can_delete', false);
            $user->can_stock_in = $request->boolean('can_stock_in', false);
            $user->can_stock_out = $request->boolean('can_stock_out', false);
        }
        
        // Only update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        return redirect()->route('users.index')->with('success', 'User information has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent admin from deleting their own account
        if ($user->id === auth()->user()->id) {
            return response()->json(['error' => 'You cannot delete your own account.'], 403);
        }
        
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}