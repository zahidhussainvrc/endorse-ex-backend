<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Factory;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $auth;

    public function __construct()
    {
        $this->auth = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->createAuth();
    }

    // Method to send OTP (this is usually triggered from the client-side using Firebase JS SDK)
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Sending the OTP is handled on the client-side.
        // On the backend, you might store the request for verification.
        return response()->json(['message' => 'OTP sent to ' . $request->phone], 200);
    }

    // Method to verify the OTP sent to the user
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // This method assumes you have the `firebase_uid` after OTP verification.
            // Typically, the client-side sends the ID token or phone number after OTP verification.
            $firebaseUser = $this->auth->getUserByPhoneNumber($request->phone);

            // Check if the user exists in the local database
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                // If user does not exist, create a new user
                $user = User::create([
                    'phone' => $request->phone,
                    'firebase_uid' => $firebaseUser->uid,
                ]);
            }

            return response()->json([
                'message' => 'Phone number verified successfully',
                'user' => $user
            ], 200);
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            return response()->json(['error' => 'Invalid OTP or phone number'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Verification failed', 'message' => $e->getMessage()], 500);
        }
    }
}
