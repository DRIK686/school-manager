<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        if ($request->action) {
            $query->where('action', $request->action);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'actions'));
    }

    // Delete a single log entry
    public function destroy(ActivityLog $log)
    {
        $summary = $log->action . ' — ' . $log->description;
        $log->delete();

        \App\Services\ActivityLogger::log('activity-log.delete', 'Deleted 1 activity log entry (' . $summary . ').');

        return back()->with('success', 'Log entry deleted.');
    }

    // Bulk purge all logs older than a given date
    public function purge(Request $request)
    {
        $request->validate([
            'before_date' => 'required|date',
        ]);

        $query = ActivityLog::whereDate('created_at', '<', $request->before_date);
        $count = $query->count();
        $query->delete();

        \App\Services\ActivityLogger::log('activity-log.purge', "Purged {$count} activity log entries older than {$request->before_date}.");

        return back()->with('success', "Purged {$count} log entries older than " . \Carbon\Carbon::parse($request->before_date)->format('d M Y') . '.');
    }
}
