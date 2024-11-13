<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use app\Models\User;
// use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Factory;
use App\Models\User;
use App\Models\HeartConsumption;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Auth\UserRecord;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Heart;
use DB;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class AuthController extends Controller
{

    private $firebaseAuth;

    public function __construct()
    {
        // Initialize Firebase using the credentials file path from .env
        $firebase = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

            $this->firebaseAuth = $firebase->createAuth();
            // dd( $this->firebaseAuth);


        }

    public function register(Request $request)
    {
         // Validation rules
            $validator = Validator::make($request->all(), [
                // 'phone' => 'required|string|max:15',
                'phone' => [
                    'required',
                    'string',
                    'regex:/^\+\d{1,3}\s\(\d{3}\)\s\d{3}-\d{4}$/', // Match format +1 (415) 555-2671
                ],

                'otp' => 'nullable|string|min:4|max:6',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'api_token' => 'nullable|string|max:80',
                'hearts' => 'nullable|integer|min:0',
                'profile_complete' => 'nullable|boolean',
                'fcm_token' => 'nullable|string',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

              // Format the phone number to E.164 format as a string
                try {
                    $phoneUtil = PhoneNumberUtil::getInstance();
                    $phoneNumberObject = $phoneUtil->parse($request->phone, "US"); // Replace "US" with the correct country code
                    $formattedPhone = $phoneUtil->format($phoneNumberObject, PhoneNumberFormat::E164);

                    // Check if the formatted phone number is within the valid length
                    if (strlen($formattedPhone) > 15) {
                        return response()->json(['status' => 'error', 'message' => 'Phone number is too long for E.164 format'], 422);
                    }
                } catch (\libphonenumber\NumberParseException $e) {
                    return response()->json(['status' => 'error', 'message' => 'Invalid phone number format'], 422);
                }



            // Handle profile image upload if it exists
            $profile_image = null;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImagePath = $profileImage->store('public/profile_images');
                $profile_image = Storage::url($profileImagePath);
            }

            $referral_code = strtoupper(Str::random(8)); // Generate a unique referral code
            $token = Str::random(60); // Generate API token

            try {
                // Create Firebase Auth user
                // $firebaseUser = $this->firebaseAuth->createUser([
                //     'email' => $request->email,
                //     'emailVerified' => false,
                //     'phoneNumber' => $formattedPhone,
                //     'password' => $request->password,
                //     'displayName' => $request->name,
                //     'photoUrl' => $profile_image,
                //     'disabled' => false,
                // ]);


                $firebaseUser = $this->firebaseAuth->createUser([
                    'email' => $request->email,
                    'emailVerified' => false,
                    'phoneNumber' => $formattedPhone,
                    'password' => $request->password,
                    'displayName' => $request->name,
                    // 'photoUrl' => $profile_image ? $profile_image : null,
                    'disabled' => false,
                ]);

                $referralCode = $request->input('referral_code');
                $referredBy = User::where('referral_code', $referralCode)->first();

                // Save user to local database
                $user = new User([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'profile_image' => $profile_image,
                    'api_token' => $token,
                    'fcm_token' => $request->fcm_token,
                    'password' => bcrypt($request->password),
                    'referral_code' => $referral_code,
                    'referred_by' => $referredBy ? $referredBy->referral_code : null,

                    // 'hearts' => $request->hearts ?? 0,
                    'profile_complete' => $request->profile_complete ?? false,
                ]);


                 // Check if a referral code was provided and is valid
                 if ($request->has('referral_code')) {
                    $referrer = User::where('referral_code', strtoupper($request->referral_code))->first();
                    if ($referrer) {
                        Heart::create([
                            'user_id' => $referrer->id,
                            'hearts' => 1,
                            'status' => 'earned',
                        ]);
                    }
                }

                if ($user->save()) {
                    $this->sendOtp($user->email);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Successfully created user!',
                        'data' => $user,
                        'firebase_uid' => $firebaseUser->uid,
                        'token' => $token
                    ], 201);
                }

            } catch (\Kreait\Firebase\Exception\AuthException $e) {
                return response()->json(['status' => 'error', 'message' => 'Firebase Auth error: ' . $e->getMessage()], 500);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()], 500);
            }
    }
    // public function register(Request $request)
    // {
    //     // Validation rules
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|string|max:15',
    //         'otp' => 'nullable|string|min:4|max:6',
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users,email',
    //         'password' => 'required|string|min:6|confirmed',
    //         'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'api_token' => 'nullable|string|max:80',
    //         // 'referral_code' => 'nullable|string|max:10',
    //         'hearts' => 'nullable|integer|min:0',
    //         'profile_complete' => 'nullable|boolean',
    //         'fcm_token' => 'nullable|string',
    //     ]);

    //     // Check if validation fails
    //     if ($validator->fails()) {
    //         return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    //     }

    //     // Initialize profile_image as null
    //     $profile_image = null;

    //     // Handle profile image upload if it exists
    //     if ($request->hasFile('profile_image')) {
    //         $profileImage = $request->file('profile_image');
    //         $profileImagePath = $profileImage->store('public/profile_images');
    //         // Convert storage path to URL format
    //         $profile_image = Storage::url($profileImagePath);
    //     }

    //     // if (empty($request->referral_code)) {
    //         // Generate a unique referral code, e.g., a random alphanumeric string
    //         // }

    //         $referral_code = strtoupper(Str::random(8)); // Example: "A1B2C3D4"

    //     // Generate API token
    //     $token = Str::random(60);

    //     // Create a new user instance with validated data
    //     $user = new User([
    //         'name' => $request->name,
    //         'phone' => $request->phone,
    //         'email' => $request->email,
    //         'profile_image' => $profile_image,
    //         'api_token' => $token,
    //         'fcm_token' => $request->fcm_token,
    //         'password' => bcrypt($request->password),
    //         'referral_code' => $referral_code,
    //         'hearts' => $request->hearts ?? 0,
    //         'profile_complete' => $request->profile_complete ?? false,
    //     ]);

    //     // Attempt to save the user
    //     if ($user->save()) {

    //         $this->sendOtp($user->email);

    //         // Return a success response with the user data and token
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Successfully created user!',
    //             'data' =>$user,
    //             'token' => $token
    //         ], 201);
    //     } else {
    //         // Return an error response if the user creation failed
    //         return response()->json(['status' => 'error', 'message' => 'Failed to create user'], 400);
    //     }
    // }
    public function login(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:6',
    ]);

    // If validation fails, return error
    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    // Attempt to authenticate the user using the provided credentials
    $credentials = $request->only('email', 'password');
    if (!Auth::attempt($credentials)) {
        return response()->json(['status' => 'error', 'message' => 'Invalid email or password'], 401);
    }

    // Get the authenticated user
    $user = Auth::user();

    // consumeHeart
    // $this->consumeHeart();

    // Generate a new API token for the user
    $user->api_token = Str::random(60);
    $user->save();

    // Return a success response with the user details and the API token
    return response()->json([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'api_token' => $user->api_token,
        ],
    ]);
}

public function socialLogin(Request $request)
{
    // Validate the request to ensure a Firebase token is provided
    $validator = Validator::make($request->all(), [
        'firebase_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    try {
        // Verify the Firebase ID token
        $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->firebase_token);
        $firebaseUid = $verifiedIdToken->claims()->get('sub');

        // Retrieve Firebase user data using the UID
        $firebaseUser = $this->firebaseAuth->getUser($firebaseUid);

        // Check if the user already exists in the local Laravel database
        $user = User::where('email', $firebaseUser->email)->first();

        if (!$user) {
            // If user doesn't exist, create a new user
            $user = User::create([
                'name' => $firebaseUser->displayName ?? 'Firebase User',
                'email' => $firebaseUser->email,
                'phone' => $firebaseUser->phoneNumber ?? null,
                'profile_image' => $firebaseUser->photoUrl ?? null,
                'api_token' => Str::random(60), // Generate a new API token
            ]);
        } else {
            // If user exists, refresh API token
            $user->api_token = Str::random(60);
            $user->save();
        }

        // consumeHeart
        // $this->consumeHeart();


        // Return a success response with user details and API token
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
                'api_token' => $user->api_token,
            ],
        ]);
    } catch (\Kreait\Firebase\Exception\Auth\InvalidToken $e) {
        // Handle invalid Firebase token
        return response()->json(['status' => 'error', 'message' => 'Invalid Firebase token'], 401);
    } catch (\Exception $e) {
        // Handle general error
        return response()->json(['status' => 'error', 'message' => 'Login failed', 'error' => $e->getMessage()], 500);
    }
}


        // Method to send OTP (this is usually triggered from the client-side using Firebase JS SDK)
        public function sendOtp($email)
        {
            // $validator = Validator::make($request->all(), [
            //     'email' => 'required|string|email|max:255',
            // ]);

            // if ($validator->fails()) {
            //     return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            // }

            // Generate a random 6-digit OTP code
            $otp = rand(100000, 999999);

            // Find or create a user with the provided email
            $user = User::firstOrCreate(
                ['email' => $email],
                ['otp' => $otp]
            );

            // Update the OTP in the user's record
            $user->otp = $otp;
            $user->save();

            // Send the OTP to the user's email using Laravel's Mail facade.
            // OtpMail::raw('Your OTP code is: ' . $otp, function ($message) use ($request) {
            //     $message->to($request->email)
            //             ->subject('Your OTP Verification Code');
            // });

            // Send the OTP email
            Mail::to($email)->send(new OtpMail($otp));

            // return response()->json(['message' => 'OTP sent to ' . $request->email], 200);
            return response()->json(['message' => 'OTP sent to ' . $email], 200);
        }


        // Method to verify the OTP sent to the user
        public function verifyOtp(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'otp' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            // Check if the OTP matches the one in the database
            if ($user->otp !== $request->otp) {
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP or OTP expired'], 400);
            }

            // Mark the email as verified
            $user->email_verified_at = now();
            $user->otp = null; // Clear the OTP after successful verification
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully',
                'user' => $user
            ], 200);
        }




          // public function forgotpassword(Request $request){

        // }

        public function logout(Request $request)
        {
            // Get the currently authenticated user
            $user = Auth::guard('api')->user();

            // Check if the user is authenticated
            if ($user) {
                // Invalidate the user's API token
                $user->api_token = null;
                $user->save();

                // Return a success response
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logged out successfully',
                ], 200);
            }

            // Return an error if the user is not authenticated
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }
        public function forgotPassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $user = User::where('email', $request->email)->first();

            // Generate OTP or Token and send via email
            $otp = rand(100000, 999999); // Example OTP generation
            $user->otp = $otp;
            $user->save();

            // Send OTP to user's email
            Mail::to($user->email)->send(new OtpMail($otp));

            return response()->json(['status' => 'success', 'message' => 'OTP sent to your email.'], 200);
        }

        public function resetPassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|exists:users,email',
                'otp' => 'required|numeric',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $user = User::where('email', $request->email)->where('otp', $request->otp)->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP or email'], 401);
            }

            // Update password in Laravel database
            $user->password = bcrypt($request->new_password);
            $user->otp = null; // Clear OTP after use
            $user->save();

            // Update password in Firebase
            try {
                $firebaseUser = $this->firebaseAuth->getUserByEmail($request->email);
                $this->firebaseAuth->changeUserPassword($firebaseUser->uid, $request->new_password);
            } catch (\Kreait\Firebase\Exception\AuthException $e) {
                return response()->json(['status' => 'error', 'message' => 'Firebase error: ' . $e->getMessage()], 500);
            }

            return response()->json(['status' => 'success', 'message' => 'Password successfully reset'], 200);
        }



}
