<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VehicleRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        
        $ownerName = trim($user->first_name . ' ' . $user->last_name) ?: 'Center Owner';
        $centerName = trim($user->center_name ?? '') ?: 'PUC Center';
        $initial = strtoupper(substr($ownerName ?: 'C', 0, 1));
        
        $profileImage = $user->profile_image ? asset($user->profile_image) : null;
        $status = strtolower($user->status ?? 'active');
        $isVerified = (bool) $user->mobile_verified;
        
        $createdAt = $user->created_at ? $user->created_at->format('d-m-Y') : '—';
        $updatedAt = $user->updated_at ? $user->updated_at->format('d-m-Y') : '—';

        // Stats for the sidebar
        $totalRecords = VehicleRecord::where('user_id', $user->id)->count();
        $activeRecords = VehicleRecord::where('user_id', $user->id)
            ->where('expiry_date', '>=', Carbon::today()->toDateString())
            ->count();

        return view('profile', compact(
            'user', 'ownerName', 'centerName', 'initial', 'profileImage',
            'status', 'isVerified', 'createdAt', 'updatedAt',
            'totalRecords', 'activeRecords'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'center_name' => ['required', 'string', 'max:180'],
            'center_address' => ['required', 'string', 'max:500'],
            'profile_image' => ['nullable', 'image', 'max:2048'], // 2MB Max
            'whatsapp_language' => ['nullable', 'string', 'in:en,guj'],
            'auto_reminder_days' => ['nullable', 'array'],
            'auto_reminder_days.*' => ['integer', 'in:1,3,7,15,30'],
        ]);

        $data = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'center_name' => $request->input('center_name'),
            'center_address' => $request->input('center_address'),
            'whatsapp_language' => $request->input('whatsapp_language', 'en'),
            'auto_reminder_days' => $request->input('auto_reminder_days', []),
        ];

        if ($request->hasFile('profile_image')) {
            // Delete old file if exists
            if ($user->profile_image) {
                $oldPath = str_replace('storage/', '', $user->profile_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            $path = $request->file('profile_image')->store('profile-photos', 'public');
            $data['profile_image'] = 'storage/' . $path;
        }

        $user->update($data);

        return redirect()->route('profile')->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'different:current_password'],
            'confirm_password' => ['required', 'string', 'same:new_password'],
        ], [
            'new_password.regex' => 'The new password must contain at least one uppercase letter and one number.',
            'new_password.different' => 'The new password cannot be the same as your current password.',
            'confirm_password.same' => 'The passwords do not match.',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return redirect()->route('profile')->with('success', 'Password changed successfully.');
    }
}
