<?php

namespace App\Http\Controllers\api\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Rating; // Assuming you have a rating model
use App\Models\Heart;
use Illuminate\Support\Facades\Auth;


class ProfileController extends Controller
{
    public function viewOwnRatings()
    {
        $user = Auth::user();
        $ratings = Rating::where('ex_partner_id', $user->id)->get();

        return response()->json(['status' => 'success', 'data' => $ratings]);
    }

    // Search other profiles
    public function searchProfiles(Request $request)
    {
        $query = User::query();

        if ($request->has('full_name')) {
            $query->where('full_name', 'LIKE', '%' . $request->input('full_name') . '%');
        }

        if ($request->has('age_min') && $request->has('age_max')) {
            $ageMin = now()->year - $request->input('age_max');
            $ageMax = now()->year - $request->input('age_min');
            $query->whereBetween('birthdate', [$ageMin, $ageMax]);
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        $profiles = $query->get();

        return response()->json(['status' => 'success', 'data' => $profiles]);
    }

    // Unlock profile
    public function unlockProfile($id)
    {
        $user = Auth::user();

        // Check if the user has enough hearts
        $heart = Heart::where('user_id', $user->id)->first();

        if ($heart->hearts < 1) {
            return response()->json(['message' => 'Not enough hearts'], 403);
        }

        // Unlock the profile
        $user->hearts -= 1; // Deduct one heart
        $user->save();

        // Record the profile unlock
        $user->unlocks()->attach($id);

        return response()->json(['status' => 'success', 'message' => 'Profile unlocked']);
    }

    // View a specific user's profile
    public function viewProfile($id)
    {
        $user = Auth::user();
        $targetUser = User::findOrFail($id);

        // Check if the profile is locked
        $isLocked = !$user->unlocks()->where('target_user_id', $targetUser->id)->exists();

        if ($isLocked) {
            return response()->json(['status' => 'error', 'message' => 'Profile is locked'], 403);
        }

        // Return user profile
        return response()->json(['status' => 'success', 'data' => $targetUser]);
    }
}
