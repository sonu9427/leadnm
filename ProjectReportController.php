<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\PropertyType;
use App\Models\Project;
use Carbon\Carbon;

class ProjectReportController extends Controller
{
    public function index()
    {

        $property_types = PropertyType::get();
        $projects = Project::get();
        return view('index', compact('property_types', 'projects'));
    }

    public function getProjectReportM(Request $request)
    {
        $propertyTypeId = $request->input('property_type_id');
        $projectId = $request->input('project_id');

        $query = Contact::with(['address', 'propertyType', 'projects.products']);

        if ($propertyTypeId) {
            $query->where('property_type_id', $propertyTypeId);
        }

        if ($projectId) {
            $query->where('id', $projectId);
        }

        $projects = $query->get();

        // echo "<pre>";
        // print_R($projects->toArray());
        // exit;

        // Format the data for the table
        $data = $projects->map(function ($contact) {
            return [
                'month' => $contact->created_at->format('M'),
                'date' => $contact->created_at->format('Y-m-d'),
                'first_name' => $contact->first_name,
                'state' => $contact->address->state,
                'property_type' => $contact->propertyType->property_type,
                'project' => $contact->projects->map(function ($project) {
                    return $project->name;
                })->implode(', '),
                'products' => $contact->projects->map(function ($project) {
                    return $project->products->map(function ($product) {
                        return '- ' . $product->name;
                    })->implode('<br>');
                })->implode('<br>')

            ];
        });

        return response()->json($data);
    }

    public function getProjectReport(Request $request)
    {
        $propertyTypeId = $request->input('property_type_id');
        $projectId = $request->input('project_id');
        $dateFilter = $request->input('date_filter');

        $query = Contact::with(['address', 'propertyType', 'projects.products'])
            ->whereMonth('created_at', now()->month) // Filter by current month
            ->whereYear('created_at', now()->year);

        if ($propertyTypeId) {
            $query->where('property_type_id', $propertyTypeId);
        }

        if ($projectId) {
            $query->where('id', $projectId);
        }

        if ($dateFilter) {
            try {
                $dateFilter = Carbon::createFromFormat('Y-m-d', $dateFilter); // Convert to Carbon instance
                $query->whereDate('created_at', $dateFilter->format('Y-m-d'));
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format'], 400);
            }
        }

        $projects = $query->get();

        // echo "<pre>";
        // print_R($projects->toArray());
        // exit;

        // Format the data for the table
        $data = $projects->map(function ($contact) {
            return [
                'month' => $contact->created_at->format('M'),
                'date' => $contact->created_at->format('Y-m-d'),
                'first_name' => $contact->first_name,
                'state' => $contact->address->state,
                'property_type' => $contact->propertyType->property_type,
                'project' => $contact->projects->map(function ($project) {
                    return $project->name;
                })->implode(', '),
                'products' => $contact->projects->map(function ($project) {
                    return $project->products->map(function ($product) {
                        if ($product->category_id == 1) {
                            return '- ' . $product->name;
                        }
                    })->implode('<br>');
                })->implode('<br>')

            ];
        });

        return response()->json($data);
    }
}
