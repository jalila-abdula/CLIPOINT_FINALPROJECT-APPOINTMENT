<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $role = trim((string) $request->string('role'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->latest()
            ->paginate(10);

        return view('users.index', [
            'users' => $users->withQueryString(),
            'search' => $search,
            'role' => $role,
            'roles' => User::ROLES,
            'registerableRoles' => User::REGISTERABLE_ROLES,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return back()->with('status', 'User account updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === request()->user()->id, 422, 'You cannot delete your own account.');

        $user->delete();

        return back()->with('status', 'User account deleted successfully.');
    }
}
