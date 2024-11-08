<?php

namespace App\Http\Controllers\api\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\HeartConsumption;
use App\Models\Heart;
use Illuminate\Support\Facades\Auth;

class HeartConsumeControll extends Controller
{
    public function consumeHeart()
    {
        // dd("Testing ");

        $user = Auth::user();

        // Check if the user has an "earned" heart available to spend
        // $heart = Heart::where('user_id', $user->id)
        //             ->where('status', 'spend')
        //             ->where('hearts', '>', 0)
        //             ->first();

        // if ($heart && $heart->spendHearts(1)) {
            // Record this heart consumption as a new row with a "earned" status
            Heart::create([
                'user_id' => $user->id,
                'hearts' => 1,
                'status' => 'earned',
            ]);

            // Track daily consumption for streak purposes
            $today = now()->startOfDay();
            HeartConsumption::firstOrCreate(
                ['user_id' => $user->id, 'consumed_at' => $today]
            );

            // Check if the user has a 5-day streak
            $streakCount = HeartConsumption::where('user_id', $user->id)
                ->where('consumed_at', '>=', now()->subDays(4)->startOfDay())
                ->count();

            if ($streakCount === 5) {
                // Award bonus hearts for 5-day streak as a new row entry
                for ($i = 0; $i < 3; $i++) {
                    Heart::create([
                        'user_id' => $user->id,
                        'hearts' => 1,
                        'status' => 'earned',
                    ]);
                }
            }

            return response()->json([  'status' => 'success', 'message' => 'Heart consumed successfully'], 200);
        // }

        // return response()->json(['status' => 'error', 'message' => 'Not enough hearts to consume'], 403);
    }

      // Earn heart by referring a friend
      public function earnHeartByReferral(Request $request)
      {
          $referrer = User::find($request->referrer_id);

          if ($referrer) {
              Heart::create([
                  'user_id' => $referrer->id,
                  'hearts' => 1,
                  'status' => 'earned',
              ]);

              return response()->json(['status' => 'success', 'message' => 'Heart earned through referral'], 200);
          }

          return response()->json(['status' => 'error', 'message' => 'Invalid referrer'], 404);
      }

      // Earn heart by connecting Facebook
      public function earnHeartByConnectingFacebook()
      {
          $user = Auth::user();
        // dd($user->facebook_connected);
          if (!$user->facebook_connected) {
              $user->update(['facebook_connected' => true]);

              Heart::create([
                  'user_id' => $user->id,
                  'hearts' => 1,
                  'status' => 'earned',
              ]);

              return response()->json(['status' => 'success', 'message' => 'Heart earned by connecting Facebook'], 200);
          }

          return response()->json(['status' => 'error', 'message' => 'Facebook already connected'], 400);
      }

      // Earn heart by completing profile
      public function earnHeartByCompletingProfile()
      {
          $user = Auth::user();

          if (!$user->profile_complete) {
              $user->update(['profile_complete' => true]);

              Heart::create([
                  'user_id' => $user->id,
                  'hearts' => 1,
                  'status' => 'earned',
              ]);

              return response()->json(['status' => 'success', 'message' => 'Heart earned by completing profile'], 200);
          }

          return response()->json(['status' => 'error', 'message' => 'Profile already complete'], 400);
      }

}
