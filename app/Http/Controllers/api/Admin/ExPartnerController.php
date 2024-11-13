<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ExPartner;

class ExPartnerController extends Controller
{
    public function __construct()
    {
        // Apply authentication middleware to all methods except for index and store if needed
        // $this->middleware('auth:api')->except(['index', 'store']);
        $this->middleware('auth:api');
    }

    // Store a new ex-partner profile
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'age_range' => 'required|in:18-25,26-35,36-45,46+',
            'birthday' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female,non-binary',
            'relationship_duration' => 'required|in:6 months - 1 year,1 year - 3 years,3 years - 5 years,5 years - 10 years,10+ years',
            'college' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            // 'phone_number' => 'nullable|string|max:20',
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(\+1[ -]?|\+1\s)?\(\d{3}\)\s\d{3}[- ]?\d{4}$/'
            ],

        ]);

        $exPartner = auth()->user()->exPartners()->create($validated);

        return response()->json(['status' => 'success','message'=>'ex-partner added successfully', 'data' => $exPartner], 201);
    }

    // Retrieve all ex-partner profiles for a user
    public function index()
    {
        // $exPartners = auth()->user()->exPartners;
        // $exPartners = ExPartner::all();
         $exPartners = ExPartner::latest()->get();
        return response()->json(['status' => 'success', 'data' => $exPartners]);
    }
    public function show($id)
    {
        // Retrieve the ex-partner associated with the authenticated user
        $exPartner = auth()->user()->exPartners()->find($id);
        // Check if the ex-partner exists
        if (!$exPartner) {
            return response()->json(['status' => 'error', 'message' => 'Ex-partner not found'], 404);
        }

        // Return the ex-partner details
        return response()->json(['status' => 'success', 'data' => $exPartner], 200);
    }

    // Update an ex-partner profile
    public function update(Request $request, $id)
    {


        $exPartner = ExPartner::findOrFail($id);

        // $this->authorize('update', $exPartner); // Make sure only the owner can update
        // return $exPartner;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'age_range' => 'required|string',
            'birthday' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female,other',
            'relationship_duration' => 'required|string',
            'college' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            // 'phone_number' => 'nullable|string|max:20',
            'phone_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(\+1[ -]?|\+1\s)?\(\d{3}\)\s\d{3}[- ]?\d{4}$/'
            ],

            'profession' => 'nullable|string|max:255',
        ]);

        $exPartner->update($validated);

        return response()->json(['status' => 'success', 'data' => $exPartner]);
    }

    // Delete an ex-partner profile
    public function destroy($id)
    {
        $exPartner = ExPartner::findOrFail($id);
        $this->authorize('delete', $exPartner);

        $exPartner->delete();

        return response()->json(['status' => 'success', 'message' => 'Ex-partner deleted']);

    }

    public function updatePartnerStatus(Request $request, $id)
    {
        // Validate the request to ensure 'status' is provided and is valid
        // $request->validate([
        //     'status' => 'required|in:active,inactive,suspended', // adjust allowed statuses as needed
        // ]);

        // Find the partner by ID
        $partner = ExPartner::find($id);

        // Check if the partner exists
        if (!$partner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Partner not found'
            ], 404);
        }

        // Update the partner's status
        // $partner->status = $request->status;
        $partner->status = $partner->status == 'active' ? 'in-active' : 'active';
        $partner->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Partner status updated successfully',
            'data' => $partner
        ], 200);
    }

}
