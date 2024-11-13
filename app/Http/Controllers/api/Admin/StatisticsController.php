<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ExPartner; // Assuming there's an ExPartner model
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;


class StatisticsController extends Controller
{
    public function getUserCount(): JsonResponse
    {
        $count = User::count();
        return response()->json(['count' => $count]);
    }

    public function getExPartnerCount(): JsonResponse
    {
        $count = ExPartner::count();
        return response()->json(['count' => $count]);
    }

    public function getMonthlyUserCounts()
    {
        // Initialize an array with all months set to zero
        $monthlyCounts = array_fill(1, 12, 0);

        // Fetch counts from the database
        $users = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Replace zero values with actual data
        foreach ($users as $user) {
            $monthlyCounts[$user->month] = $user->count;
        }

        // Convert to a format with month names and counts
        $response = [];
        $months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        foreach ($monthlyCounts as $monthIndex => $count) {
            $response[] = [
                'month' => $months[$monthIndex - 1],
                'count' => $count
            ];
        }

        return response()->json($response);
    }

    public function getMonthlyExPartnerCounts()
    {
        $monthlyCounts = array_fill(1, 12, 0);

        $exPartners = ExPartner::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($exPartners as $exPartner) {
            $monthlyCounts[$exPartner->month] = $exPartner->count;
        }

        // Convert to a format with month names and counts
        $response = [];
        $months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        foreach ($monthlyCounts as $monthIndex => $count) {
            $response[] = [
                'month' => $months[$monthIndex - 1],
                'count' => $count
            ];
        }

        return response()->json($response);
    }


}
