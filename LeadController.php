<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        // Get current date range
        $currentDate = Carbon::now()->format('Y-m-d');
        $dateFrom = Carbon::parse($currentDate)->startOfDay();
        $dateTo = Carbon::parse($currentDate)->endOfDay();

        // Check if the request is AJAX
        if ($request->ajax()) {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $dateFrom;
            $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $dateTo;

            $leadCounts = Lead::select('lead_name', DB::raw('COALESCE(COUNT(projects.id), 0) as total_lead_name_count'))
                ->leftJoin('projects', 'leads.id', '=', 'projects.lead_id')
                ->whereBetween('projects.created_at', [$dateFrom, $dateTo])
                ->groupBy('leads.id', 'leads.lead_name')
                ->having('total_lead_name_count', '>', 0)  // Filter out leads with a count of 0
                ->get();

            return response()->json($leadCounts);
        }

        // If not AJAX, return the standard view with current date counts
        $leadCounts = Lead::select('lead_name', DB::raw('COALESCE(COUNT(projects.id), 0) as total_lead_name_count'))
            ->leftJoin('projects', 'leads.id', '=', 'projects.lead_id')
            ->whereBetween('projects.created_at', [$dateFrom, $dateTo])
            ->groupBy('leads.id', 'leads.lead_name')
            ->having('total_lead_name_count', '>', 0)  // Filter out leads with a count of 0
            ->get();

        return view('lead.index', compact('leadCounts'));
    }
}
