<?php

namespace App\Http\Controllers\api\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function userProfile($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function UpdateUserProfile(Request $request,$id)
    {
        $user = User::find($id);
        // $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

           // Validate the input data
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|regex:/^\+?(\d{1,3})?\s?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{4}$/',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8|confirmed',
                // 'profile_image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048', // Image validation
            ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function countReferredUsers($userId)
    {
        // Get the referral code of the user you want to check
        $referralCode = User::where('id', $userId)->value('referral_code');


        if (!$referralCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found or does not have a referral code'
            ], 404);
        }

        // Count users where 'referred_by' matches the referral code
        $totalReferredUsers = User::where('referred_by', $referralCode)->count();

        // dd($totalReferredUsers);
        // $count = $this->countReferredUsers($userId);

        // dd($count);



        return response()->json([
            'status' => 'success',
            'referral_code' => $referralCode,
            'total_referred_users' => $totalReferredUsers
        ]);
    }

}
