<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomTrait;
use Illuminate\Http\Request;

class TraitsController extends Controller
{

    public function __construct()
    {
        // Apply authentication middleware to all methods except for index and store if needed
        // $this->middleware('auth:api')->except(['index', 'store']);
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $users = CustomTrait::latest()->get();
        // $exPartners = auth()->user()->exPartners;
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);

    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $customTrait = CustomTrait::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Trait added successfully.',
            'data' => $customTrait
        ], 201);
    }

      // Update an existing trait
      public function update(Request $request)
      {

        // return $request;



          $trait = CustomTrait::findOrFail($request->id); // Find the trait or fail
        // return $trait;

          $validated = $request->validate([
              'name' => 'required|string|max:255',
          ]);

          $trait->update([
              'name' => $validated['name'],
          ]);

          return response()->json([
              'status' => 'success',
              'message' => 'Trait updated successfully.',
              'data' => $trait,
          ]);
      }

      // Delete a trait
      public function destroy($id)
      {
          $trait = CustomTrait::findOrFail($id); // Find the trait or fail
          $trait->delete(); // Delete the trait

          return response()->json([
              'status' => 'success',
              'message' => 'Trait deleted successfully.',
          ]);
      }

}
