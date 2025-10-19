<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Only allow admin users to access announcement management
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Unauthorized access. Admin privileges required.'], 403);
                }
                abort(403, 'Unauthorized access. Admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Announcement::with('creator');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $announcements = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('client.announcement.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('client.announcement.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'event_date' => 'nullable|date'
        ]);

        $validated['created_by'] = Auth::id();

        Announcement::create($validated);

        return redirect()->route('client.announcement.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $announcement = Announcement::with('creator')->findOrFail($id);
        return view('client.announcement.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        return view('client.announcement.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'event_date' => 'nullable|date'
        ]);

        $announcement->update($validated);

        return redirect()->route('client.announcement.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return redirect()->route('client.announcement.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    /**
     * Toggle announcement status between draft and published
     */
    public function toggleStatus(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->status = $announcement->status === 'published' ? 'draft' : 'published';
        $announcement->save();

        return redirect()->route('client.announcement.index')
            ->with('success', 'Announcement status updated successfully!');
    }

    /**
     * Bulk publish announcements
     */
    public function bulkPublish(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id'
        ]);

        $count = Announcement::whereIn('id', $request->ids)
                           ->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'message' => "{$count} announcement(s) published successfully!",
            'count' => $count
        ]);
    }

    /**
     * Bulk delete announcements
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id'
        ]);

        $count = Announcement::whereIn('id', $request->ids)->count();
        Announcement::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} announcement(s) deleted successfully!",
            'count' => $count
        ]);
    }
}