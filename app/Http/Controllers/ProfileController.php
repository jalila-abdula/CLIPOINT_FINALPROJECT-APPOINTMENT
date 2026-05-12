<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        Appointment::markReadyForNoShow();

        $user = $request->user();

        $managedAppointments = Appointment::query()
            ->with(['client', 'staff'])
            ->when(
                $user->isStaff(),
                fn ($query) => $query->where('staff_id', $user->id),
                fn ($query) => $query->where('created_by', $user->id)
            );

        return view('profile.edit', [
            'user' => $user,
            'roleLabel' => match ($user->role) {
                User::ROLE_ADMIN => 'Admin',
                User::ROLE_RECEPTIONIST => 'Receptionist',
                User::ROLE_STAFF => 'Staff',
                default => 'User',
            },
            'profileStats' => [
                'completed' => (clone $managedAppointments)
                    ->where('status', Appointment::STATUS_COMPLETED)
                    ->count(),
                'upcoming' => (clone $managedAppointments)
                    ->whereDate('appointment_date', '>=', today())
                    ->whereIn('status', Appointment::BOOKED_STATUSES)
                    ->count(),
                'service_records' => ServiceRecord::query()
                    ->where('staff_id', $user->id)
                    ->count(),
            ],
            'recentAppointments' => (clone $managedAppointments)
                ->whereDate('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
