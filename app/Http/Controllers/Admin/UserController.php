<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', ['users' => User::orderBy('name')->paginate(15)]);
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 403, 'You cannot change your own role.');

        $data = $request->validate([
            'role' => ['required', Rule::in(['super_admin', 'agent', 'customer'])],
        ]);

        $user->role = $data['role'];
        $user->save();

        return back()->with('status', "{$user->name} is now {$data['role']}.");
    }
}