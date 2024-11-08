<?php

namespace App\Http\Controllers\api\user;

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
        $exPartners = auth()->user()->exPartners()->with(['ratings', 'comments'])->get();

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

    public function searchProfiles(Request $request)
    {
        $query = ExPartner::query();

        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->input('full_name') . '%');
        }
        if ($request->filled('age_range')) {
            // $ages = explode('-', $request->input('age_range'));
            // $query->whereBetween('age_range', [(int) $ages[0], (int) $ages[1]]);
            $ages = explode('-', $request->input('age_range'));

            // $abc = [(int) $ages[0], '-',(int) $ages[1]];

            // dd( $ages[0].'-'.$ages[1]);

            $query->where('age_range', $ages[0].'-'.$ages[1]);

        }
        if ($request->filled('birthday')) {
            $query->whereDate('birthday', $request->input('birthday'));
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        $profiles = $query->get();
        return response()->json(['status' => 'success', 'data' => $profiles]);
        // return response()->json($profiles);

    }


}
