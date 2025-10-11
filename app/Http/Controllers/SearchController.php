<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\User;
use App\Models\Service;
use App\Models\Status;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        // Search Leads
        $leads = Lead::with(['service', 'status', 'assignedUser'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        foreach ($leads as $lead) {
            $results->push([
                'id' => $lead->id,
                'title' => $lead->name,
                'description' => $lead->company_name ? "Company: {$lead->company_name}" : "Lead #{$lead->id}",
                'category' => 'leads',
                'url' => route('leads.show', $lead->id),
                'subtitle' => $lead->phone ?: $lead->email,
                'status' => $lead->status->name ?? 'Unknown',
                'service' => $lead->service->name ?? 'Unknown'
            ]);
        }

        // Search Users
        $users = User::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results->push([
                'id' => $user->id,
                'title' => $user->name,
                'description' => $user->email,
                'category' => 'users',
                'url' => route('users.show', $user->id),
                'subtitle' => ucfirst($user->role),
                'status' => $user->is_admin ? 'Admin' : 'User'
            ]);
        }

        // Search Services
        $services = Service::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($services as $service) {
            $results->push([
                'id' => $service->id,
                'title' => $service->name,
                'description' => 'Service',
                'category' => 'services',
                'url' => route('services.show', $service->id),
                'subtitle' => 'Service',
                'status' => 'Active'
            ]);
        }

        // Search Statuses
        $statuses = Status::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($statuses as $status) {
            $results->push([
                'id' => $status->id,
                'title' => $status->name,
                'description' => 'Status',
                'category' => 'statuses',
                'url' => route('statuses.show', $status->id),
                'subtitle' => 'Status',
                'status' => 'Active'
            ]);
        }

        // Sort results by relevance (exact matches first, then partial matches)
        $sortedResults = $results->sortBy(function ($item) use ($query) {
            $titleMatch = stripos($item['title'], $query) === 0 ? 0 : 1;
            $descMatch = stripos($item['description'], $query) === 0 ? 0 : 1;
            return $titleMatch + $descMatch;
        });

        return response()->json([
            'results' => $sortedResults->values()->toArray(),
            'total' => $sortedResults->count(),
            'query' => $query
        ]);
    }

    public function quickSearch(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Quick lead search
        $leadCount = Lead::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('company_name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })->count();

        if ($leadCount > 0) {
            $results[] = [
                'category' => 'leads',
                'count' => $leadCount,
                'label' => 'Leads',
                'url' => route('leads.index') . '?search=' . urlencode($query)
            ];
        }

        // Quick user search
        $userCount = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })->count();

        if ($userCount > 0) {
            $results[] = [
                'category' => 'users',
                'count' => $userCount,
                'label' => 'Users',
                'url' => route('users.index') . '?search=' . urlencode($query)
            ];
        }

        return response()->json(['results' => $results]);
    }
}
