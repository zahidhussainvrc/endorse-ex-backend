<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ExPartner;
use App\Models\Commit;

class CommitController extends Controller
{

    public function allCommits()
    {
        $commits = Commit::with(['user', 'exPartner'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $commits
        ]);
    }
    public function index($exPartnerId)
    {


        $commits = Commit::where('ex_partner_id', $exPartnerId)->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $commits
        ]);
    }

    /**
     * Store a newly created commit for an ex-partner.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $exPartnerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $exPartnerId)
    {
        $request->validate([
            // 'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
            // 'status' => 'required|in:pending,completed' // Example statuses
            // 'approve', 'inapprove'
        ]);

        $user_id = Auth::user()->id;

        $commit = Commit::create([
            'user_id' => $user_id,
            'ex_partner_id' => $exPartnerId,
            'message' => $request->message,
            // 'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $commit
        ]);
    }

    /**
     * Update the specified commit.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $commit = Commit::findOrFail($id);

        $request->validate([
            'message' => 'sometimes|required|string|max:255',
            // 'status' => 'sometimes|required|in:pending,completed'
        ]);

        $commit->update($request->only('message'));

        return response()->json([
            'status' => 'success',
            'data' => $commit
        ]);
    }

    /**
     * Remove the specified commit from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $commit = Commit::findOrFail($id);
        $commit->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Commit deleted successfully'
        ]);
    }

    public function updateUserStatus($id)
        {
            $commit = Commit::findOrFail($id);
            if (!empty($commit)) {
                if ($commit->status == 'inapprove') {
                    $commit->status = 'approve';
                    $commit->save();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Commit approved successfully'
                    ]);
                } else {
                    $commit->status = 'inapprove';
                    $commit->save();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Commit inapproved successfully'
                    ]);
                }
            }
        }

}
