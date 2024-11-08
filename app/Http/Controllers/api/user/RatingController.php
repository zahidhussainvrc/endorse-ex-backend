<?php

namespace App\Http\Controllers\api\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Rating;
use App\Models\ExPartner; // Assuming you have this model
use App\Models\Heart; // Assuming you have this model
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{

    public function __construct()
    {
        // Apply authentication middleware to all methods except for index and store if needed
        // $this->middleware('auth:api')->except(['index', 'store']);
        $this->middleware('auth:api');
    }

   // Store a new rating for an ex-partner
   public function store(Request $request)
   {
       $validated = $request->validate([
           'ex_partner_id' => 'required|exists:ex_partners,id',
           'trait_id' => 'required|exists:custom_traits,id',
           'rating' => 'required|integer|min:1|max:5',
       ]);

       $user = Auth::user();

       // Check if user has any earned hearts available
       $hearts = Heart::where('user_id', $user->id)
                       ->where('status', 'earned')
                       ->where('hearts', '>', 0)
                       ->orderBy('created_at', 'asc') // Optional: Use oldest hearts first
                       ->get();

       // Check if the user has at least one heart to spend
       if ($hearts->isEmpty()) {
           return response()->json(['message' => 'Not enough hearts'], 403);
       }

       // Deduct one heart from the available heart entries
       $remainingHearts = 1; // Number of hearts we need to deduct for this rating
       foreach ($hearts as $heart) {
           if ($heart->hearts >= $remainingHearts) {
               $heart->hearts -= $remainingHearts;
               $remainingHearts = 0;
           } else {
               $remainingHearts -= $heart->hearts;
               $heart->hearts = 0;
           }

           // Update the status if hearts are depleted in this record
           if ($heart->hearts == 0) {
               $heart->status = 'spent';
           }

           $heart->save();

           if ($remainingHearts == 0) {
               break; // Stop once we've spent the required heart
           }
       }

       // Create the rating entry
       $rating = Rating::create([
           'ex_partner_id' => $validated['ex_partner_id'],
           'user_id' => $user->id,
           'trait_id' => $validated['trait_id'],
           'rating' => $validated['rating'],
       ]);

       return response()->json(['status' => 'success', 'data' => $rating], 201);
   }


   // Optional: Retrieve ratings for a specific ex-partner
   public function show($id)
   {
    // return $id;

       $ratings = Rating::where('ex_partner_id', $id)->get();

       return response()->json(['status' => 'success', 'data' => $ratings]);

   }
}
